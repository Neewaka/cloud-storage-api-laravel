<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /**
     * Create new user using data from request
     * 
     * @param Request $request
     * @return Response
     */
    public function register(Request $request) {

        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('storageapitoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
        ];

        return response($response, 201);
    }

    /**
     * Log in user using data from request
     * 
     * @param Request $request
     * @return Response
     */
    public function login(Request $request) {
        
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        //check email
        $user = User::where('email', $fields['email'])->first();

        //check password
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $token = $user->createToken('storageapitoken')->plainTextToken;

        $response = [
            'status' => true,
            'user' => $user,
            'token' => $token,
        ];

        return response($response, 201);
    }

    /**
     * Logging out the user and destroing all his current tokens
     * 
     * @param Request $request
     * @return string
     */
    public function logout(Request $request){
        auth()->user()->tokens()->delete();

        return [
            'message' => 'User logged out'
        ];
    }
}
