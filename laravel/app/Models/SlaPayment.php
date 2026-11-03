<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SlaPayment extends Model
{
    protected $fillable = [
        'subscription_id', 'period_from', 'period_to', 'amount', 'paid_at',
        'method', 'reference', 'note', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date:Y-m-d',
            'period_to'   => 'date:Y-m-d',
            'paid_at'     => 'date:Y-m-d',
            'amount'      => 'decimal:2',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function activationCode(): HasOne
    {
        return $this->hasOne(ActivationCode::class);
    }
}
