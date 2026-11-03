# karimgroup-sla

4axiz SLA activation system. Reports in the client software only work while an SLA, paid quarterly, is active.

| Folder | What it is |
|---|---|
| `laravel/` | API for sla.4axiz.com. These files go into a fresh Laravel 11+ project |
| `nextjs/` | Admin panel: clients, payments, generate or cancel activation codes |
| `client-karimgroup/` | Changed and added files in the karimgroup software (CodeIgniter 2, PHP 5.6) |
| `tools/` | Key generator and offline license maker. The token format is described in `tools/README.md` |

Deployment steps are in [SETUP.md](SETUP.md).

> The RSA private key (`private.pem`) is **not** in this repository and must never be committed.
> Copy it to the server by hand: `storage/app/sla/private.pem`.
