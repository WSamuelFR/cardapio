<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'photo_path',
        'price',
        'is_promotion',
        'is_combo',
        'promotional_price',
        'is_available',
    ];

    protected $appends = [
        'effective_price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'is_promotion' => 'boolean',
            'is_combo' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getEffectivePriceAttribute(): string
    {
        if ($this->is_promotion && $this->promotional_price !== null && $this->promotional_price > 0) {
            return (string) $this->promotional_price;
        }

        return (string) $this->price;
    }
}

