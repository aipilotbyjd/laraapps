<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        return response()->json([
            'user' => $user,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => \Carbon\Carbon::parse($token->expires_at)->toDateTimeString()
        ], Response::HTTP_CREATED);
    }

    /**
     * Authenticate user and return token
     */
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $user->tokens()->delete();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        return response()->json([
            'user' => $user,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => \Carbon\Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
    }

    /**
     * Get authenticated user details
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh user token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->token()->revoke();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => \Carbon\Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
    }
}
