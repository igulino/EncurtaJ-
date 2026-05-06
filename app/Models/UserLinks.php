<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLinks extends Model
{
        protected $table = 'user_links';
        protected $fillable = ['link_generated', 'given_link', 'user_id', 'name'];
    
        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }
    
        public function linkClicks()
        {
            return $this->hasMany(LinkClicks::class, 'user_link_id');
        }
    //
}
