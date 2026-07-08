<?php

namespace App\Http\Controllers;

use App\Models\LinkClicks;
use App\Models\UserLinks;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class LinkClicksController extends Controller
{
    public static function StoreClickData()
    {
        $keys = Redis::keys('link_generated:*:clicks');

        foreach ($keys as $key) {
            $data = Redis::lrange($key, 0, -1);
            $slug = str_replace(['link_generated:', ':clicks'], '', $key);
            $userLinkId = self::resolveUserLinkId($slug);

            Log::info('Processing click data for slug: ' . $slug);
            Log::info('User Link ID: ' . ($userLinkId ?? 'not found'));

            if (!$data) {
                continue;
            }

            if (!$userLinkId) {
                Log::warning("Skipping click data because user link does not exist for slug: {$slug}");
                Redis::del($key);
                continue;
            }

            foreach ($data as $clickData) {
                $click = json_decode($clickData);

                if (!$click || empty($click->link_clicked)) {
                    Log::warning("Skipping invalid click payload for slug: {$slug}");
                    continue;
                }

                $locationData = self::getLocationData($click->ip ?? null);

                Log::info("this is hash: " . $click->hash . "and repeated " . $click->repeated);
                
                LinkClicks::create([
                    'link_clicked' => $click->link_clicked,
                    'clicked_at' => $click->clicked_at ?? now(),
                    'browser' => $click->browser ?? null,
                    'ip' => '9999999999',
                    'device' => $click->device ?? null,
                    'location' => $locationData['location'],
                    'latitude' => $locationData['latitude'],
                    'longitude' => $locationData['longitude'],
                    'referer' => $click->referer ?? null,
                    'user_link_id' => $userLinkId,
                    'hash' => $click->hash,
                    'repeated' => $click->repeated
                ]);

                Log::info("Click data saved for slug: {$slug}");
            }

            Redis::del($key);
        }
    }

    public static function Datacleaner()
    {
        $keys = Redis::keys('link_generated:*:clicks');

        foreach ($keys as $key) {
            Redis::del($key);
        }

        Log::info('- Data clean -');
    }

    private static function resolveUserLinkId(string $slug): ?int
    {
        $userLinkId = Redis::hget("link_generated:{$slug}", 'user_link_id');

        if ($userLinkId && UserLinks::whereKey($userLinkId)->exists()) {
            return (int) $userLinkId;
        }

        $userLink = UserLinks::where('link_generated', 'like', '%/' . $slug)->first();

        if (!$userLink) {
            Redis::del("link_generated:{$slug}");
            return null;
        }

        Redis::hmset("link_generated:{$slug}", [
            'given_link' => $userLink->given_link,
            'user_link_id' => $userLink->id,
        ]);

        return $userLink->id;
    }

    private static function getLocationData(?string $ip): array
    {
        $locationData = [
            'location' => 'Unknown',
            'latitude' => null,
            'longitude' => null,
        ];

        if (!$ip) {
            return $locationData;
        }

        try {
            $response = Http::get("http://ip-api.com/json/189.38.95.95");
        } catch (Throwable $exception) {
            Log::warning('Could not fetch IP location data: ' . $exception->getMessage());

            return $locationData;
        }

        if (!$response->successful() || $response['status'] === 'fail') {
            return $locationData;
        }

        return [
            'location' => trim(($response['city'] ?? '') . ' ' . ($response['region'] ?? '')) ?: 'Unknown',
            'latitude' => isset($response['lat']) ? number_format($response['lat'], 7, '.', '') : null,
            'longitude' => isset($response['lon']) ? number_format($response['lon'], 7, '.', '') : null,
        ];
    }
}
