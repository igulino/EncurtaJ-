<?php 

namespace App\Http\services;

use Illuminate\Http\Request;
use App\Models\LinkClicks;
use App\Models\UserLinks;
use Illuminate\Support\Facades\Log;


class RealDashServices {
    function main(Request $request, $vault) {
        
        $user = $request->user();
        
        $id = UserLinks::where('link_generated', 'http://localhost:8000/' . $vault)->first(['id', 'user_id', 'created_at', 'link_generated', 'name']);
        
        if (!$id) {
            return [
                'vault' => null,
                'linkClicks' => collect(),
            ];
        }


        if ($id->user_id == $user->id) {
            Log::info("equals " . $id->user_id . " " . $user->id);
            $linckclicks = LinkClicks::where('user_link_id', $id->id)->get();

            $linckclicks->each(function ($linkClick) use ($id) {
                $linkClick->link_created = $id->created_at;
            });
            Log::info("user_link_id: " . $linckclicks);
            return [
                'vault' => $id,
                'linkClicks' => $linckclicks,
            ];
        }
       
        return [
            'vault' => null,
            'linkClicks' => collect(),
        ]; 
    }
}