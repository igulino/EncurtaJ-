<?php

namespace App\Http\Controllers;

use App\Http\services\RealDashServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class RealDash extends Controller {
    
    public function Dash(Request $request, $vault) {
        
        Log::info("this .. .");


        $v1 = urldecode($vault);
        $data = (new RealDashServices)->main($request, $v1);
        

        return view('RealDash', [
            'linkClicks' => $data['linkClicks'],
            'vault' => $data['vault'],
        ]);
    }
}
