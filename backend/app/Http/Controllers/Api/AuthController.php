<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);

        activity()
            ->causedBy($user)
            ->withProperties(['email' => $user->email])
            ->log('user_registered');

        return response()->json([
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['email' => auth()->user()->email])
            ->log('user_logged_in');

        return response()->json([
            'user' => new UserResource(auth()->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function users()
    {
        $users = User::all();
        return UserResource::collection($users);
    }
}
