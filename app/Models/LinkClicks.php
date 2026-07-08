<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkClicks extends Model
{
        protected $table = 'link_clicks';
        protected $fillable = ['link_clicked', 'clicked_at', 'browser', 'ip', 'device', 'location', 'latitude', 'longitude', 'referer', 'user_link_id', 'hash', 'repeated'];

        protected $casts = [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'repeated' => 'boolean',
        ];
    
        public function userLink()
        {
            return $this->belongsTo(UserLinks::class, 'user_link_id');
        }

        
    //
}
