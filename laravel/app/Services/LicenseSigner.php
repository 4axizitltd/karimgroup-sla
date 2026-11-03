<?php

namespace App\Services;

use App\Models\ActivationCode;
use RuntimeException;

/**
 * Produces the signed license token the client software verifies.
 * Must stay byte-compatible with karimgroup/application/libraries/Sla_license.php:
 *   token = base64url(json) . "." . base64url(RSA-SHA256(base64url(json)))
 */
class LicenseSigner
{
    public function sign(ActivationCode $code, string $installId): string
    {
        $code->loadMissing('subscription.client', 'subscription.application');

        $payload = [
            'v'     => 1,
            'lid'   => $code->license_id,
            'cid'   => $code->subscription->client->code,
            'app'   => $code->subscription->application->code,
            'iid'   => $installId,
            'from'  => $code->valid_from->toDateString(),
            'until' => $code->valid_until->toDateString(),
            'iat'   => now()->format('Y-m-d H:i:s'),
        ];

        $body = $this->b64url(json_encode($payload, JSON_UNESCAPED_SLASHES));

        if (! openssl_sign($body, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign license: ' . openssl_error_string());
        }

        return $body . '.' . $this->b64url($signature);
    }

    private function privateKey()
    {
        $path = config('sla.private_key_path');
        if (! is_readable($path)) {
            throw new RuntimeException("SLA private key not found at {$path}");
        }
        $key = openssl_pkey_get_private(file_get_contents($path));
        if (! $key) {
            throw new RuntimeException('SLA private key is invalid');
        }
        return $key;
    }

    private function b64url(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }
}
