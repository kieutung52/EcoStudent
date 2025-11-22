<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'rule_id',
        'admin_id',
        'note'
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

