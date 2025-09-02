<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());
        $token = $user->createToken($request->input('device_name','api'))->plainTextToken;
        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken($request->input('device_name','api'))->plainTextToken;
        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $user = $request->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password invalid'], 422);
        }
        $user->update(['password' => $data['password']]);
        return response()->json(['message' => 'Password updated']);
    }
}
