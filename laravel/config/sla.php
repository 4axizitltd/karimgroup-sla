<?php

return [
    // RSA private key (PEM) used to sign licenses. Copy 4axiz_SLA\keys\private.pem here.
    // Never commit this file.
    'private_key_path' => env('SLA_PRIVATE_KEY_PATH', storage_path('app/sla/private.pem')),

    // Default length of one SLA period in months
    'period_months' => 3,

    // Show "expiring soon" in the dashboard this many days before expiry
    'warn_days' => 15,
];
