# 4axiz SLA tools

## Files
The keys are deliberately kept **outside this repository**, in `4axiz_SLA\keys\` (or the folder in the `SLA_KEY_DIR` environment variable).
- `keys\private.pem`: **SECRET**. Copy it only to the sla.4axiz.com Laravel server (`storage/app/sla/private.pem`). Never put it in client software or in git.
- `keys\public.pem`: already embedded in `karimgroup/application/config/sla.php`.
- `keygen.php`: creates the key pair. Already run once. Running it again would make every issued license stop working.
- `make_license.php`: creates a license key by hand (offline, or for testing before the API is ready).

```
D:\4axiz\xampp\php\php.exe D:\4axiz\4axiz_SLA\code\tools\make_license.php KG HRM 2026-11-01 2027-01-31
```
The client pastes the printed key at **Setting -> SLA Activation**.

## License token format (Laravel must produce exactly this)
```
token   = base64url(json_payload) + "." + base64url(signature)
sig     = openssl_sign(base64url(json_payload), $sig, $privateKey, OPENSSL_ALGO_SHA256)
payload = {"v":1,"lid":"A1B2C3D4E5F6","cid":"KG","app":"HRM","iid":"*","from":"2026-11-01","until":"2027-01-31","iat":"2026-11-03 10:00:00"}
```
- `cid`/`app`: client code and application code. They must match `sla_client_code`/`sla_app_code` in the client's config.
- `iid`: installation ID shown on the client's SLA page. `*` means any installation.
- base64url means `+/` is replaced by `-_` and trailing `=` is removed.

## API contract for sla.4axiz.com (client sends form-encoded POST and expects JSON)
| Endpoint | Request fields | Response |
|---|---|---|
| `POST /api/activate` | code, client_code, app_code, install_id, hostname | `{"ok":true,"license":"<token>"}` or `{"ok":false,"message":"..."}` |
| `POST /api/status` | client_code, app_code, install_id, license_id | `{"ok":true,"license":"<newest token for this install>"}` or `{"ok":false,"revoked":true,"message":"..."}` |

- `activate`: look up the short code (for example `KG-7F3A-92BC-11DE`). Check that it belongs to the client/app and is not used by another install_id. Bind it to install_id, then return a token signed with `iid` = install_id.
- `status`: called about once a day. Return the newest valid token for that install (renewals are picked up automatically). Return `revoked:true` to disable the license.
- After the API is live, set `$config['sla_online_enabled'] = TRUE;` in the client's `config/sla.php`.
