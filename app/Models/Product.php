<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ImageUploadTrait; // Import Trait

class Product extends Model
{
    use HasFactory, ImageUploadTrait;

    protected $fillable = [
        'post_id', 'category_id', 'name', 'price', 
        'quantity', 'image', 'description', 'is_sold'
    ];

    // Sự kiện model để tự động xóa ảnh khi xóa record
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($product) {
            // Gọi hàm từ Trait để xóa ảnh
            if ($product->image) {
                $product->deleteImage($product->image);
            }
        });
    }

    public function post() 
    { 
        return $this->belongsTo(Post::class); 
    }
    
    public function category() 
    { 
        return $this->belongsTo(Category::class); 
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}