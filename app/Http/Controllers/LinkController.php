<?php

namespace App\Http\Controllers;

use App\Models\UserLinks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LinkController extends Controller
{
    private static $UserLinks = UserLinks::class;

    public static function LinkCreation(string $paramName, string $paramLink)
    {

        $generatedLink = self::normalizeUrl($paramLink);

        self::$UserLinks::create([
            'given_link' => $paramLink,
            'name' => $paramName,
            'link_generated' => $generatedLink[0],
            'user_id' => auth()->user()->id,
        ]);
        // Store mapping (given_link + user_link_id) as JSON in the hash `link_generated` field
        $userLinkRecord = self::$UserLinks::where('link_generated', $generatedLink[0])->first();
        $userLinkId = $userLinkRecord ? $userLinkRecord->id : null;

        if ($userLinkId) {
            /*Redis::hmset('link_generated', $generatedLink[1], json_encode([
                'given_link' => $paramLink,
                'user_link_id' => $userLinkId,
            ]));*/

            Redis::hmset("link_generated:{$generatedLink[1]}", [
                'given_link' => $paramLink,
                'user_link_id' => $userLinkId,
            ]);
        }

        // Log the stored JSON for debugging
        Log::info('Link created: ' . Redis::hget('link_generated', $generatedLink[1]));
        return $generatedLink[0];

    }


    private static function normalizeUrl(string $paramLink): array
    {
        $paramLink = trim($paramLink);

        $urlComponents = parse_url($paramLink);
        $id = substr(uniqid(), 0, 15);
        $ReturnLink = 'https://encurtaj-production.up.railway.app/' . $id;
        //https://encurtaj-production.up.railway.app
        //Log::info('Generated link: ' . $ReturnLink);

        return [$ReturnLink, $id];
    }
}
