<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_document_encrypted',
        'delivery_cep',
        'delivery_address',
        'delivery_complement',
        'delivery_reference',
        'status',
        'motoboy_confirmed_at',
        'customer_confirmed_at',
        'food_rating',
        'delivery_rating',
        'review_comment',
        'allow_public_review',
        'review_show_name',
        'reviewed_at',
        'motoboy_id',
        'total',
        'notes',
        'payment_method',
        'needs_change',
        'change_for',
    ];

    /**
     * Oculta o documento criptografado de qualquer resposta de API para proteger a privacidade
     */
    protected $hidden = [
        'customer_document_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'needs_change' => 'boolean',
            'change_for' => 'decimal:2',
            'motoboy_confirmed_at' => 'datetime',
            'customer_confirmed_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'food_rating' => 'integer',
            'delivery_rating' => 'integer',
            'allow_public_review' => 'boolean',
            'review_show_name' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function motoboy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'motoboy_id');
    }
}

