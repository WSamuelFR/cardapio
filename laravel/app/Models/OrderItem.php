<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'product_id',
        'product_name_snapshot',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected static function booted(): void
    {
        static::creating(function ($item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

