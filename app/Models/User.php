<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'university_id',
        'role',
        'status',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
            'email' => $this->email,
        ];
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }



    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function conversationsAsUserOne()
    {
        return $this->hasMany(Conversation::class, 'user_one');
    }

    public function conversationsAsUserTwo()
    {
        return $this->hasMany(Conversation::class, 'user_two');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
