<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkClicks extends Model
{
        protected $table = 'link_clicks';
        protected $fillable = ['link_clicked', 'clicked_at', 'browser', 'ip', 'device', 'location', 'referer', 'user_link_id'];
    
        public function userLink()
        {
            return $this->belongsTo(UserLinks::class, 'user_link_id');
        }

        
    //
}
