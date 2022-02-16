<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Hash;


class AuthenticationController extends Controller
{
    private $token = 'paystar-client-token';

    // register user
    public function register(Request $request) {
        $fields = $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);

        $token = $user->createToken($this->token)->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token
        ];
    }


    // login user
    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (! $user  ||  !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Bad credentials'
            ], 401);
        }

        $token = $user->createToken($this->token)->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token
        ];
    }


    // logout user
    public function logout() {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'User logged out'
        ];
    }
}