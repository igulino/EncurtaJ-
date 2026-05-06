<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Log;


class CadController extends Controller
{

    public function showCadForm()
    {
        return view('Cadastro');
    }
    


}