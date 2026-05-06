<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class userController extends Controller
{

    protected static $user = User::class; 

    public static function Test(){
        $users = User::all();
        return view('Teste', compact('users'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $user = self::$user::where('email', $request->input('email'))->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
           
            return redirect()->intended('/dashboard'); 
        }
        }else {
            //Log::warning('Falha na autenticação', ['email' => $request->input('email')]);
            return back()->withErrors([
                'email' => 'Credenciais inválidas.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function Cadastro()
    {
        return view('Cadastro');
    }

    public function CadastroSubmit(Request $request)
    {
        
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
        ]);
        Log::info('Usuário ' . $validatedData['email'] . ' cadastrado com sucesso.');
        self::$user::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

       
        return redirect('/login')->with('success', 'Cadastro realizado com sucesso. Faça login para continuar.');
    }
    
    
}