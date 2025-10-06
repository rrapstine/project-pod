<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponses;

    public function show()
    {
        $user = $this->currentUser();

        return UserResource::make($user);
    }

    public function update(UpdateUserRequest $request)
    {
        $user = $this->currentUser();
        $user->update($request->validated());

        return $this->ok('Profile updated successfully', UserResource::make($user));
    }

    public function destroy(Request $request)
    {
        $user = $this->currentUser();
        $name = $user->name;

        $user->delete();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->success("Profile for {$name} deleted successfully", [], 204);
    }
}
