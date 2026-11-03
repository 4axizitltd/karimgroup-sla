<?php
/**
 * 4axiz SLA - create a signed license key (offline / testing).
 * The Laravel backend (sla.4axiz.com) will produce exactly the same format.
 *
 *   php make_license.php <client_code> <app_code> <valid_from> <valid_until> [install_id]
 *   php make_license.php KG HRM 2026-11-01 2027-01-31
 *
 * install_id = "*" (default) means the key works on any installation.
 *
 * TOKEN FORMAT:  base64url(json_payload) . "." . base64url(RSA-SHA256 signature of the first part)
 * payload = {"v":1,"lid":"...","cid":"KG","app":"HRM","iid":"*","from":"Y-m-d","until":"Y-m-d","iat":"Y-m-d H:i:s"}
 */

if ($argc < 5) {
    fwrite(STDERR, "Usage: php make_license.php <client_code> <app_code> <valid_from Y-m-d> <valid_until Y-m-d> [install_id]\n");
    exit(1);
}

list(, $cid, $app, $from, $until) = $argv;
$iid = isset($argv[5]) ? $argv[5] : '*';

foreach (array($from, $until) as $d) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
        fwrite(STDERR, "Date must be Y-m-d: $d\n");
        exit(1);
    }
}

// Keys live OUTSIDE the repo: 4axiz_SLA\keys (../../keys) or $SLA_KEY_DIR
$keyDir = getenv('SLA_KEY_DIR') ?: dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'keys';
$privatePem = @file_get_contents($keyDir . '/private.pem');
if (!$privatePem) {
    fwrite(STDERR, "{$keyDir}\\private.pem not found.\n");
    exit(1);
}

function b64url($s) { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

$payload = array(
    'v'     => 1,
    'lid'   => strtoupper(bin2hex(openssl_random_pseudo_bytes(6))),
    'cid'   => strtoupper($cid),
    'app'   => strtoupper($app),
    'iid'   => $iid,
    'from'  => $from,
    'until' => $until,
    'iat'   => date('Y-m-d H:i:s'),
);

$body = b64url(json_encode($payload));
if (!openssl_sign($body, $sig, $privatePem, OPENSSL_ALGO_SHA256)) {
    fwrite(STDERR, "openssl_sign failed\n");
    exit(1);
}

echo $body . '.' . b64url($sig) . "\n";
