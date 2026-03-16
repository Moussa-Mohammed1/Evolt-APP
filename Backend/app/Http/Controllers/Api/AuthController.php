<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'there already an registered user with this email'
            ], 409);
        }
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered Successfully',
            'user'=> $user,
            'token' => $token
        ], 201);
    }

    public function login(LoginUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }
        if (!Hash::check($request->password , $user->password)) {
            return response()->json([
                'message' => 'password correct'
            ], 422);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'connected successfully',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'disconnected successfully'
        ]);
    }
}
