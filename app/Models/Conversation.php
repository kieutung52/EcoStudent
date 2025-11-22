<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['user_one', 'user_two'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Lấy tin nhắn mới nhất để hiển thị preview
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }
}