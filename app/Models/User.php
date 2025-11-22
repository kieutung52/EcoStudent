<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; // JWT Interface

/**
 * User Model - Quản lý thông tin người dùng
 * 
 * JWT AUTHENTICATION FLOW:
 * ========================
 * 1. REGISTER/LOGIN:
 *    - User đăng ký/đăng nhập với email và password
 *    - Password được hash bằng bcrypt (tự động qua cast 'hashed')
 *    - Hệ thống tạo JWT token chứa user_id và các claims khác
 *    - Token được trả về cho client trong response header hoặc body
 * 
 * 2. AUTHENTICATION:
 *    - Client gửi request kèm token trong header: Authorization: Bearer {token}
 *    - Middleware 'auth:api' (JWT) sẽ:
 *      a. Extract token từ header
 *      b. Verify token signature và expiration
 *      c. Decode token để lấy user_id
 *      d. Load User từ database
 *      e. Attach User vào request ($request->user())
 * 
 * 3. AUTHORIZATION:
 *    - Kiểm tra role (USER/ADMIN) để phân quyền
 *    - Kiểm tra status (ACTIVE/BANNED/WARNING/SHUT_DOWN) để xác định trạng thái tài khoản
 *    - Middleware CheckAdmin kiểm tra role === 'ADMIN'
 * 
 * 4. PASSWORD HASHING:
 *    - Password được hash tự động khi tạo/update nhờ cast 'hashed'
 *    - Hash sử dụng bcrypt algorithm (cost factor 10)
 *    - Khi login, Hash::check() so sánh password plain text với hash trong DB
 * 
 * 5. TOKEN REFRESH:
 *    - JWT token có thời gian hết hạn (TTL)
 *    - Client có thể refresh token bằng refresh token endpoint
 *    - Token mới được tạo với thời gian hết hạn mới
 */
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
        'password' => 'hashed', // Tự động hash password khi tạo/update
        'is_active' => 'boolean',
    ];

    /**
     * JWT Subject Interface Methods
     * =============================
     * getJWTIdentifier(): Trả về unique identifier của user (thường là id)
     * - Được sử dụng để tạo JWT token claims
     * - Token sẽ chứa user_id này để sau này có thể load user
     * 
     * getJWTCustomClaims(): Trả về custom claims muốn thêm vào token
     * - Có thể thêm role, status, email, etc.
     * - Các claims này sẽ được encode vào token payload
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Trả về user id
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
            'email' => $this->email,
        ];
    }

    // Relationships
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
        return $this->hasMany(Order::class, 'user_id'); // Đơn mình mua
    }

    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id'); // Đơn mình bán
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
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
        return $this->hasMany(Review::class, 'reviewer_id'); // Đánh giá mình viết
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id'); // Đánh giá nhận được
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
