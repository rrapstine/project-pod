<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponses;

    public function register(RegisterUserRequest $request)
    {
        $user = User::create([
            ...$request->validated(),
            'password' => Hash::make($request->validated()['password']),
        ]);

        Auth::login($user);

        return $this->success('User created', [
            'user' => UserResource::make($user),
        ], 201);
    }

    public function login(LoginUserRequest $request)
    {
        if (! Auth::attempt($request->validated())) {
            return $this->error('Invalid credentials', 401);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->ok('Logged in', [
            'user' => UserResource::make(Auth::user()),
        ], 200);
    }

    public function logout(Request $request)
    {
        $username = Auth::user()->name;

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->ok($username.' was logged out successfully');
    }
}
