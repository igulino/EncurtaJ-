<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
        use HasFactory;
        
        protected $table = 'users';
        protected $fillable = ['email', 'password', 'Premium'];
        protected $hidden = ['password'];
    
        public function userLinks()
        {
            return $this->hasMany(UserLinks::class, 'user_id');
        }
    //
}
