<?php

namespace App\Features\Identity\Http\Controllers;

use App\Features\Identity\Http\Requests\LoginRequest;
use App\Features\Identity\Http\Requests\RegisterRequest;
use App\Features\Identity\Http\Resources\UserResource;
use App\Features\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return $this->tokenResponse($user, $validated['device_name'], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Les identifiants sont invalides.',
            ], 401);
        }

        return $this->tokenResponse($user, $credentials['device_name']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }

    private function tokenResponse(User $user, string $deviceName, int $status = 200): JsonResponse
    {
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'data' => [
                'user' => (new UserResource($user))->resolve(),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], $status);
    }
}
