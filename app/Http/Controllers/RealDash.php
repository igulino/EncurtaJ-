<?php

namespace App\Http\Controllers;

use App\Http\services\RealDashServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class RealDash extends Controller {
    
    public function Dash(Request $request, $vault) {
        

        $v1 = urldecode($vault);
        $data = (new RealDashServices)->main($request, $v1);
        
        
        Log::info("this data vault " . $data['vault']);

        return view('RealDash', [
            'linkClicks' => $data['linkClicks'],
            'vault' => $data['vault'],
        ]);
    }
}
