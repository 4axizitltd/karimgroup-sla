<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'subscription_id', 'action', 'client_code', 'app_code', 'code',
        'install_id', 'ip', 'success', 'message', 'created_at',
    ];

    protected function casts(): array
    {
        return ['success' => 'boolean', 'created_at' => 'datetime'];
    }
}
