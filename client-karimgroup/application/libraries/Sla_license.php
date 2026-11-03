<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 4axiz SLA license check (PHP 5.6 compatible).
 *
 * License token = base64url(json payload) . "." . base64url(RSA-SHA256 signature)
 * Payload: v, lid, cid, app, iid, from, until, iat
 *
 * Local state is stored in a JSON file (no database table needed):
 *   install_id, license, activated_at, last_seen, last_sync_ok, last_sync_try
 *
 * API (sla.4axiz.com), form-encoded POST, JSON response:
 *   {api}/activate  code, client_code, app_code, install_id, hostname
 *                   -> {"ok":true,"license":"<token>"} | {"ok":false,"message":"..."}
 *   {api}/status    client_code, app_code, install_id, license_id
 *                   -> {"ok":true,"license":"<latest token>"} | {"ok":false,"revoked":true,"message":"..."}
 */
class Sla_license {

    const ACTIVE       = 'active';
    const NONE         = 'none';
    const EXPIRED      = 'expired';
    const NOT_STARTED  = 'not_started';
    const INVALID      = 'invalid';
    const CLOCK        = 'clock';
    const SYNC_NEEDED  = 'sync_required';

    private $cfg;
    private $state;
    private static $cached = null;

    public function __construct($params = array()) {
        $config = array();
        include APPPATH . 'config/sla.php';
        $this->cfg = array_merge($config, is_array($params) ? $params : array());
    }

    /* ------------------------------------------------------------------ */
    /*  Public API                                                         */
    /* ------------------------------------------------------------------ */

    /** Does the given module require an active SLA? */
    public function is_protected_module($module) {
        return in_array(strtolower((string) $module), array_map('strtolower', $this->cfg['sla_protected_modules']));
    }

    /**
     * Current license status.
     * @return array status, valid(bool), message, until, days_left, warn(bool), payload, install_id
     */
    public function check($allow_sync = true) {
        if (self::$cached !== null) {
            return self::$cached;
        }
        $this->load_state();
        $now = time();

        // Detect the system clock being turned back
        if (!empty($this->state['last_seen']) && $now < $this->state['last_seen'] - 86400) {
            return self::$cached = $this->result(self::CLOCK, null,
                'System date/time is incorrect (earlier than ' . date('d M Y', $this->state['last_seen']) . '). Please correct the computer date.');
        }
        if (empty($this->state['last_seen']) || $now - $this->state['last_seen'] > 300) {
            $this->state['last_seen'] = max($now, (int) @$this->state['last_seen']);
            $this->save_state();
        }

        if ($allow_sync) {
            $this->maybe_sync($now);
        }

        if (empty($this->state['license'])) {
            return self::$cached = $this->result(self::NONE, null, 'No SLA license is activated.');
        }

        $payload = $this->verify_token($this->state['license'], $error);
        if (!$payload) {
            return self::$cached = $this->result(self::INVALID, null, 'SLA license is invalid: ' . $error);
        }

        // Must have reached the API recently (only when online mode is on)
        if ($this->cfg['sla_online_enabled'] && $this->cfg['sla_offline_grace_days'] > 0) {
            $last_ok = max((int) @$this->state['last_sync_ok'], (int) @$this->state['activated_at']);
            if ($now - $last_ok > $this->cfg['sla_offline_grace_days'] * 86400) {
                return self::$cached = $this->result(self::SYNC_NEEDED, $payload,
                    'Could not reach sla.4axiz.com for more than ' . $this->cfg['sla_offline_grace_days'] . ' days. Please connect the server to the internet.');
            }
        }

        $today = date('Y-m-d', $now);
        if ($today < $payload['from']) {
            return self::$cached = $this->result(self::NOT_STARTED, $payload, 'SLA license starts on ' . $this->fmt($payload['from']) . '.');
        }
        if ($today > $payload['until']) {
            return self::$cached = $this->result(self::EXPIRED, $payload, 'SLA expired on ' . $this->fmt($payload['until']) . '.');
        }
        return self::$cached = $this->result(self::ACTIVE, $payload, 'SLA active until ' . $this->fmt($payload['until']) . '.');
    }

    /**
     * Activate with either a short activation code (online) or a full license key (offline).
     * @return array ok(bool), message
     */
    public function activate($input) {
        $input = trim(preg_replace('/\s+/', '', (string) $input));
        if ($input === '') {
            return array('ok' => false, 'message' => 'Please enter the activation code.');
        }
        $this->load_state();

        if (strpos($input, '.') !== false && strlen($input) > 100) {
            // Offline license key
            $payload = $this->verify_token($input, $error);
            if (!$payload) {
                return array('ok' => false, 'message' => 'Invalid license key: ' . $error);
            }
            return $this->store_license($input, $payload, false);
        }

        if (!$this->cfg['sla_online_enabled']) {
            return array('ok' => false, 'message' => 'Online activation is not enabled. Please enter the full license key from 4axiz.');
        }

        $res = $this->api('activate', array(
            'code'        => strtoupper($input),
            'client_code' => $this->cfg['sla_client_code'],
            'app_code'    => $this->cfg['sla_app_code'],
            'install_id'  => $this->state['install_id'],
            'hostname'    => function_exists('gethostname') ? gethostname() : '',
        ), $http_error);

        if ($res === null) {
            return array('ok' => false, 'message' => 'Could not connect to sla.4axiz.com (' . $http_error . '). Check the internet connection.');
        }
        if (empty($res['ok']) || empty($res['license'])) {
            return array('ok' => false, 'message' => !empty($res['message']) ? $res['message'] : 'Activation code was rejected.');
        }
        $payload = $this->verify_token($res['license'], $error);
        if (!$payload) {
            return array('ok' => false, 'message' => 'Server returned an invalid license: ' . $error);
        }
        return $this->store_license($res['license'], $payload, true);
    }

    public function install_id() {
        $this->load_state();
        return $this->state['install_id'];
    }

    public function state() {
        $this->load_state();
        return $this->state;
    }

    public function online_enabled() {
        return (bool) $this->cfg['sla_online_enabled'];
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                          */
    /* ------------------------------------------------------------------ */

    private function store_license($token, $payload, $synced) {
        $now = time();
        // Never replace the current license with one that cannot be used today
        $today = date('Y-m-d', $now);
        if ($today > $payload['until']) {
            return array('ok' => false, 'message' => 'This code has already expired (' . $this->fmt($payload['until']) . ').');
        }
        if ($today < $payload['from']) {
            return array('ok' => false, 'message' => 'This code is valid from ' . $this->fmt($payload['from']) . '. Please enter it on or after that date.');
        }
        $this->state['license']      = $token;
        $this->state['activated_at'] = $now;
        if ($synced) {
            $this->state['last_sync_ok'] = $now;
        }
        if (!$this->save_state()) {
            return array('ok' => false, 'message' => 'Could not save the license file (' . $this->cfg['sla_storage_file'] . '). Check folder permissions.');
        }
        self::$cached = null;
        $status = $this->check(false);
        if (!$status['valid']) {
            return array('ok' => false, 'message' => 'License saved but not usable: ' . $status['message']);
        }
        return array('ok' => true, 'message' => 'Activated successfully. ' . $status['message']);
    }

    /** Daily background check with sla.4axiz.com (renewal / revocation). */
    private function maybe_sync($now) {
        if (!$this->cfg['sla_online_enabled']) {
            return;
        }
        $interval = $this->cfg['sla_sync_interval_hours'] * 3600;
        if ($now - (int) @$this->state['last_sync_ok'] < $interval) {
            return;
        }
        if ($now - (int) @$this->state['last_sync_try'] < 3600) {   // retry at most once an hour
            return;
        }
        $this->state['last_sync_try'] = $now;

        $current = !empty($this->state['license']) ? $this->verify_token($this->state['license'], $e) : false;
        $res = $this->api('status', array(
            'client_code' => $this->cfg['sla_client_code'],
            'app_code'    => $this->cfg['sla_app_code'],
            'install_id'  => $this->state['install_id'],
            'license_id'  => $current ? $current['lid'] : '',
        ), $http_error);

        if ($res !== null) {
            if (!empty($res['ok'])) {
                $this->state['last_sync_ok'] = $now;
                if (!empty($res['license']) && $res['license'] !== @$this->state['license']) {
                    $new = $this->verify_token($res['license'], $e);
                    if ($new && (!$current || $new['until'] >= $current['until'])) {
                        $this->state['license'] = $res['license'];
                    }
                }
            } elseif (!empty($res['revoked'])) {
                $this->state['last_sync_ok'] = $now;
                $this->state['license'] = '';
            }
        }
        $this->save_state();
    }

    /** @return array|false payload */
    private function verify_token($token, &$error) {
        $error = '';
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            $error = 'bad format';
            return false;
        }
        $sig = $this->b64url_decode($parts[1]);
        $key = openssl_pkey_get_public($this->cfg['sla_public_key']);
        if (!$key || $sig === false || openssl_verify($parts[0], $sig, $key, OPENSSL_ALGO_SHA256) !== 1) {
            $error = 'signature check failed';
            return false;
        }
        $payload = json_decode($this->b64url_decode($parts[0]), true);
        if (!is_array($payload) || empty($payload['cid']) || empty($payload['app']) || empty($payload['from']) || empty($payload['until'])) {
            $error = 'missing data';
            return false;
        }
        if (strtoupper($payload['cid']) !== strtoupper($this->cfg['sla_client_code'])
            || strtoupper($payload['app']) !== strtoupper($this->cfg['sla_app_code'])) {
            $error = 'issued for another client/application';
            return false;
        }
        $iid = isset($payload['iid']) ? $payload['iid'] : '*';
        if ($iid !== '*' && $iid !== $this->state['install_id']) {
            $error = 'issued for another installation';
            return false;
        }
        if (!isset($payload['lid'])) {
            $payload['lid'] = '';
        }
        return $payload;
    }

    private function api($endpoint, $fields, &$error) {
        $error = '';
        $url = rtrim($this->cfg['sla_api_url'], '/') . '/' . $endpoint;
        $timeout = (int) $this->cfg['sla_api_timeout'];
        $body = false;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($fields),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_HTTPHEADER     => array('Accept: application/json'),
                CURLOPT_SSL_VERIFYPEER => (bool) $this->cfg['sla_api_ssl_verify'],
                CURLOPT_SSL_VERIFYHOST => $this->cfg['sla_api_ssl_verify'] ? 2 : 0,
            ));
            $body = curl_exec($ch);
            if ($body === false) {
                $error = curl_error($ch);
            }
            curl_close($ch);
        } else {
            $ctx = stream_context_create(array(
                'http' => array('method' => 'POST', 'timeout' => $timeout, 'ignore_errors' => true,
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                    'content' => http_build_query($fields)),
                'ssl' => array('verify_peer' => (bool) $this->cfg['sla_api_ssl_verify'], 'verify_peer_name' => (bool) $this->cfg['sla_api_ssl_verify']),
            ));
            $body = @file_get_contents($url, false, $ctx);
            if ($body === false) {
                $error = 'connection failed';
            }
        }

        if ($body === false) {
            return null;
        }
        $json = json_decode($body, true);
        if (!is_array($json)) {
            $error = 'unexpected response';
            return null;
        }
        return $json;
    }

    private function load_state() {
        if ($this->state !== null) {
            return;
        }
        $file = $this->cfg['sla_storage_file'];
        $state = is_file($file) ? json_decode((string) @file_get_contents($file), true) : null;
        $this->state = is_array($state) ? $state : array();
        if (empty($this->state['install_id'])) {
            $this->state['install_id'] = strtoupper(bin2hex(openssl_random_pseudo_bytes(8)));
            $this->save_state();
        }
    }

    private function save_state() {
        $file = $this->cfg['sla_storage_file'];
        if (!is_dir(dirname($file))) {
            @mkdir(dirname($file), 0755, true);
        }
        return @file_put_contents($file, json_encode($this->state), LOCK_EX) !== false;
    }

    private function result($status, $payload, $message) {
        $days_left = null;
        if ($payload) {
            $days_left = (int) floor((strtotime($payload['until'] . ' 23:59:59') - time()) / 86400);
        }
        $valid = ($status === self::ACTIVE);
        return array(
            'status'     => $status,
            'valid'      => $valid,
            'message'    => $message,
            'from'       => $payload ? $payload['from'] : null,
            'until'      => $payload ? $payload['until'] : null,
            'days_left'  => $days_left,
            'warn'       => $valid && $days_left !== null && $days_left <= $this->cfg['sla_warn_days'],
            'payload'    => $payload,
            'install_id' => $this->state['install_id'],
        );
    }

    private function fmt($ymd) {
        return date('d M Y', strtotime($ymd));
    }

    private function b64url_decode($s) {
        $pad = strlen($s) % 4;
        if ($pad) {
            $s .= str_repeat('=', 4 - $pad);
        }
        return base64_decode(strtr($s, '-_', '+/'), true);
    }
}
