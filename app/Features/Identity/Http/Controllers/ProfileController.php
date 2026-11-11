<?php

namespace App\Features\Identity\Http\Controllers;

use App\Features\Identity\Http\Requests\UpdateProfileRequest;
use App\Features\Identity\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill($validated);

        if (array_key_exists('first_name', $validated) || array_key_exists('last_name', $validated)) {
            $user->name = trim(($validated['first_name'] ?? $user->first_name).' '.($validated['last_name'] ?? $user->last_name));
        }

        $user->save();

        return new UserResource($user);
    }
}
