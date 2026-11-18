<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'old_price',
        'category',
        'image',
        'badge',
        'stock',
        'is_active',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
