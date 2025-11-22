<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'seller_id', 
        'total_amount', 
        'payment_method', 
        'shipping_address', 
        'phone_number', 
        'status', 
        'note'
    ];

    // Người mua
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Người bán
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}