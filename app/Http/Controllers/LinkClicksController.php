<?php

namespace App\Http\Controllers;
use App\Models\LinkClicks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LinkClicksController extends Controller
{
    public static function StoreClickData()
    {
        
        $keys = Redis::keys('link_generated:*:clicks');
        foreach ($keys as $key){

        $Data = Redis::lrange($key, 0, -1);
        $slug = str_replace(['link_generated:', ':clicks'], '', $key);
        $userLinkId = Redis::hget("link_generated:{$slug}", 'user_link_id');
        // Log::info('Data found: ' . json_decode($Data[0])->link_clicked);
        Log::info('Processing click data for slug: ' . $slug);
        Log::info('User Link ID: ' . $userLinkId);

        if ($Data && $userLinkId) {
            
            LinkClicks::create([
                'link_clicked' => json_decode($Data[0])->link_clicked,
                'clicked_at' => json_decode($Data[0])->clicked_at,
                'browser' => json_decode($Data[0])->browser,
                'ip' => json_decode($Data[0])->ip,
                'device' => json_decode($Data[0])->device,
                'location' => 'Unknown',
                'referer' => json_decode($Data[0])->referer,
                'user_link_id' => $userLinkId,
            ]);
            }
        }
       
       Log::info('Current click data for slug ' . $slug . ': ' . json_encode($Data));
    }

    public static function Datacleaner(){
        $keys = Redis::keys('link_generated:*:clicks');
        foreach ($keys as $key){
            Redis::del($key);
        }
    }
}