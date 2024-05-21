<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function loginPost(Request $request)
    {
        $credetials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        if (Auth::attempt($credetials)) {
            $headers = ['Accept' => 'application/json'];
            return redirect('/home', 302, $headers)->with('success', 'Login Successful');
        }
        return back()->with('error', 'Email or Password is incorrect');
    }

    public function register()
    {
        return view('register');
    }

    public function registerPost(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('success', 'Register successfully');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
