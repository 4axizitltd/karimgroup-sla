<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = ['client_id', 'application_id', 'quarterly_amount', 'install_id', 'is_active', 'note'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'quarterly_amount' => 'decimal:2'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SlaPayment::class);
    }

    public function activationCodes(): HasMany
    {
        return $this->hasMany(ActivationCode::class);
    }

    /** Find by the codes the client software sends (KG + HRM). */
    public function scopeForCodes(Builder $q, string $clientCode, string $appCode): Builder
    {
        return $q->whereHas('client', fn ($c) => $c->where('code', strtoupper($clientCode)))
            ->whereHas('application', fn ($a) => $a->where('code', strtoupper($appCode)));
    }

    /** Last day covered by any non-revoked code. */
    public function coveredUntil(): ?string
    {
        $d = $this->activationCodes()->whereNull('revoked_at')->max('valid_until');
        return $d ? substr((string) $d, 0, 10) : null;
    }
}
