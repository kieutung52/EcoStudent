<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'reviewer_id', 'reviewed_user_id', 'rating', 'comment'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Người đánh giá (Người mua)
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Người được đánh giá (Người bán)
    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
}