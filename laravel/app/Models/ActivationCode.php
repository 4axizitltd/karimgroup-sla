<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationCode extends Model
{
    protected $fillable = [
        'subscription_id', 'sla_payment_id', 'code', 'license_id', 'valid_from', 'valid_until',
        'install_id', 'hostname', 'activated_at', 'last_seen_at', 'revoked_at', 'revoke_reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'valid_from'   => 'date:Y-m-d',
            'valid_until'  => 'date:Y-m-d',
            'activated_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'revoked_at'   => 'datetime',
        ];
    }

    protected $appends = ['status'];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SlaPayment::class, 'sla_payment_id');
    }

    public function getStatusAttribute(): string
    {
        $today = now()->toDateString();
        if ($this->revoked_at) return 'revoked';
        if ($this->valid_until->toDateString() < $today) return 'expired';
        if ($this->valid_from->toDateString() > $today) return 'upcoming';
        return $this->install_id ? 'active' : 'not_activated';
    }

    /** KG-7F3A-92BC-11DE (no 0/O/1/I to avoid confusion when typing) */
    public static function generateCode(string $clientCode): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        do {
            $groups = [];
            for ($g = 0; $g < 3; $g++) {
                $s = '';
                for ($i = 0; $i < 4; $i++) {
                    $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                }
                $groups[] = $s;
            }
            $code = strtoupper($clientCode) . '-' . implode('-', $groups);
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public static function generateLicenseId(): string
    {
        do {
            $id = strtoupper(bin2hex(random_bytes(6)));
        } while (static::where('license_id', $id)->exists());

        return $id;
    }
}
