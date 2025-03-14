<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];


    protected function avatar(): Attribute{
        return Attribute::make(get: function($value){
            return $value ? '/storage/avatars/' . $value : '/fallback-avatar.jpg';
        });
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function feedposts(){
        return $this->hasManyThrough(Post::class, Follow::class, "user_id", "user_id", "id","followeduser");
        //return intermediate table
        //that is follow table
    }

    public function followers(){
        return $this->hasMany(Follow::class, 'followeduser');
    }

    public function followingTheseUsers(){
        return $this->hasMany(Follow::class, 'user_id');
    }

    public function posts(){
        //a user has many post
        return $this->hasMany(Post::class, 'user_id');
    }
}
