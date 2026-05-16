<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'feature_list',
    'design_preference',
    'reference_app',
    'target_user',
    'business_flow',
    'additional_notes',
])]

class OrderDetail extends Model
{
    protected function casts(): array
    {
        return [
            'feature_list' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
