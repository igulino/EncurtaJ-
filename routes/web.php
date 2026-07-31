<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DashController;
use App\Http\Controllers\RealDash;
use App\Models\UserLinks;
use App\Models\LinkClicks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
//Route::get('/Test', [userController::class, 'Test']);
Route::get('/login', function () {
    return view('Login');
})->name("login");


Route::post('/Login', [userController::class, 'Login'])->name("teste");
Route::get('/cadastro', [userController::class, 'Cadastro'])->name('cadastro');
Route::post('/cadastro', [userController::class, 'CadastroSubmit'])->name('submitCadastro');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashController::class, 'index'])->name('dashboard')->middleware('web');
    Route::get('/realdash/{vault}', [RealDash::class, 'Dash'])->name('realdash')->middleware('web');
    Route::post('/logout', [userController::class, 'Logout'])->name('logout')->middleware('web');
});


//Redirecionamento de links de cria né
Route::get('/{slug}', function ($slug) {
    Log::info('Redirect route accessed with slug: ' . $slug);
    
    // Read the JSON blob stored as the field in hash `link_generated`
    $Value = Redis::hget("link_generated:{$slug}", 'given_link');
    Log::info('Redis returned value: ' . $Value);
  /*$decoded = json_decode($Value, true);
    $givenLink = is_array($decoded) && array_key_exists('given_link', $decoded) ? $decoded['given_link'] : null;
    Log::info('returned value: ' . ($givenLink ?? 'No value found'));*/
    if ($Value) {
        $agent = new Agent();
        $term = request()->ip() . $Value;

        $hash = hash('sha256', $term);
        //$ag = $agent->setUserAgent(request()->userAgent());
        $device = $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
        
        $firstClick = Redis::lindex("link_generated:{$slug}:clicks", 0);
        $userlinkInfo = UserLinks::where('link_generated', 'https://encurtaj-production.up.railway.app/' . $slug)->get();
        
        $hashExists = LinkClicks::where('user_link_id', $userlinkInfo[0]->id)->get('hash')->first();
        $hashExists = $hashExists ? json_decode($hashExists) : null;

        $firstClick = $firstClick ? json_decode($firstClick) : null;

        $storedHash = $firstClick?->hash ?? $hashExists?->hash;
        if ($storedHash) {
            Log::info("click já existe no redis ou no banco, só comparar e adicionar como repetido ou n");
            //Log::info("this is click: " . $firstClick->hash);
            
            if (hash_equals($hash, $storedHash)) {
                Log::info("comparação feita, só mandar pro redis...");
                Redis::rpush("link_generated:{$slug}:clicks", json_encode([
                    'slug' => $slug,
                    'timestamp' => now()->toDateTimeString(),
                    'link_clicked' => $Value,
                    'clicked_at' => now()->toDateTimeString(),
                    'browser' => request()->userAgent(),
                    'ip' => request()->ip(),
                    'device' => $device,
                    'location' => 'Unknown',
                    'referer' => request()->headers->get('Referer'),
                    'hash' => null,
                    'repeated' => true
                ]));
            }


        }else{
            Redis::rpush("link_generated:{$slug}:clicks", json_encode([
                
                'slug' => $slug,
                'timestamp' => now()->toDateTimeString(),
                'link_clicked' => $Value,
                'clicked_at' => now()->toDateTimeString(),
                'browser' => request()->userAgent(),
                'ip' => request()->ip(),
                'device' => $device,
                'location' => 'Unknown',
                'referer' => request()->headers->get('Referer'),
                'hash' => $hash,
                'repeated' => false

            ]));

        }
        //$test = Redis::lrange("laravel-database-link_generated:{$slug}:clicks", 0, -1);
        //Log::info('$test: ' . json_encode($test));
        return redirect()->away($Value);
    };
    
    $link = UserLinks::where('link_generated', 'like', '%/' . $slug . '%')->first();

    if ($link) {
        
        Redis::hmset("link_generated:{$slug}", [
            'given_link' => $link->given_link,
            'user_link_id' => $link->id,
        ]);

        Redis::rpush("link_generated:{$slug}:clicks", json_encode([
            'slug' => $slug,
            'timestamp' => now()->toDateTimeString(),
            'link_clicked' => $link->given_link,
            'clicked_at' => now()->toDateTimeString(),
            'browser' => request()->userAgent(),
            'ip' => request()->ip(),
            'device' => 'desktop',
            'location' => 'Unknown',
            'referer' => request()->headers->get('Referer'),

        ]));


        Log::info('Link found in database: ' . $link->given_link);
        return redirect()->away($link->given_link);
    }

    abort(404);
})->where('slug', '[A-Za-z0-9\-_]+');

