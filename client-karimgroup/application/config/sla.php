<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| 4axiz SLA activation settings
| -------------------------------------------------------------------
| Reports are only available while a valid SLA license is active.
*/

// Who this installation belongs to (must match the license issued by sla.4axiz.com)
$config['sla_client_code'] = 'KG';
$config['sla_app_code']    = 'HRM';

// Modules that require an active SLA
$config['sla_protected_modules'] = array('report');

// sla.4axiz.com API
$config['sla_online_enabled'] = TRUE;                        // sla.4axiz.com API is live
$config['sla_api_url']        = 'https://sla.4axiz.com/api';
$config['sla_api_timeout']    = 5;                            // seconds
// Responses are RSA-signed, so a forged response cannot create a license.
// Old XAMPP builds often lack a CA bundle, hence FALSE by default.
$config['sla_api_ssl_verify'] = FALSE;

// Online check frequency and how long the software may run without reaching the API
$config['sla_sync_interval_hours'] = 24;
$config['sla_offline_grace_days']  = 7;

// Show a warning this many days before expiry
$config['sla_warn_days'] = 15;

// Where the local copy of the license is stored
$config['sla_storage_file'] = APPPATH . 'sla_data/license.json';

// Public key of sla.4axiz.com (verification only - cannot create licenses)
$config['sla_public_key'] = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5FiY4REm5b98bx4zf5wa
VGGvP85bczTKSV9vETFYr2VYtjYpzAR+w16pIInSW2Y+eb4cTtsFQzbJS9XK9ifJ
V3r9od1zd+3V51BGv5rpmYYmB5HcEkAL2gNlbA7pBGNGmTc0iXgdVDUNjKmeQgNF
0G11UjFLDq4URSoLXJ12N51G/StKuVIO71HEU1j8BQFTEJsABWOmG7mQHlL7sqgu
GD3oakdeFnIrkR9X17e975zZWUFl0VbQZcLr0604LxQg/Ek99IKjanbxNcCFL5jA
WrgFPzy1oRiDkRJOYWxxrO5mR1sqZBwYdcABMFJbVNHYJ2UGCnQDj8Bnhxjd0vTm
PwIDAQAB
-----END PUBLIC KEY-----';
