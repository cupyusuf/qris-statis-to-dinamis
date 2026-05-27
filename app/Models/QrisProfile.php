<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisProfile extends Model
{
    protected $fillable = [
        'merchant_name',
        'static_payload',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }
}
