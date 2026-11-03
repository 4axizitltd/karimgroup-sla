<?php
/**
 * 4axiz SLA - RSA key pair generator (run ONCE).
 *
 *   D:\4axiz\xampp\php\php.exe D:\4axiz\4axiz_SLA\code\tools\keygen.php
 *
 * Creates:
 *   keys/private.pem  -> SECRET. Only on sla.4axiz.com (Laravel storage). Never give to clients.
 *   keys/public.pem   -> Goes into client software (application/config/sla.php).
 */

// Keys live OUTSIDE the repo: 4axiz_SLA\keys (../../keys) or $SLA_KEY_DIR
$dir = getenv('SLA_KEY_DIR') ?: dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'keys';
if (!is_dir($dir)) {
    mkdir($dir, 0700, true);
}
if (file_exists($dir . '/private.pem')) {
    fwrite(STDERR, "keys/private.pem already exists. Delete it manually if you really want new keys\n" .
        "(all codes issued with the old key will stop working).\n");
    exit(1);
}

$opts = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'digest_alg'       => 'sha256',
);
// XAMPP on Windows needs an explicit openssl.cnf
foreach (array('D:/4axiz/xampp/php/extras/openssl/openssl.cnf', 'D:/4axiz/xampp/apache/conf/openssl.cnf') as $cnf) {
    if (file_exists($cnf)) {
        $opts['config'] = realpath($cnf);
        break;
    }
}

$res = openssl_pkey_new($opts);
if (!$res) {
    fwrite(STDERR, "openssl_pkey_new failed: " . openssl_error_string() . "\n");
    exit(1);
}
openssl_pkey_export($res, $privatePem, null, $opts);
$details = openssl_pkey_get_details($res);

file_put_contents($dir . '/private.pem', $privatePem);
file_put_contents($dir . '/public.pem', $details['key']);

echo "Done.\n  {$dir}\\private.pem  (KEEP SECRET)\n  {$dir}\\public.pem\n";
