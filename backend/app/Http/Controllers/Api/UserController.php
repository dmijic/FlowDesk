<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('manage_users');

        return UserResource::collection(
            User::query()->with(['department', 'roles.permissions'])->latest()->get()
        );
    }

    public function store(StoreUserRequest $request): UserResource
    {
        Gate::authorize('manage_users');

        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? 'Password123!'),
            'department_id' => $data['department_id'] ?? null,
        ]);

        if (! empty($data['roles'])) {
            $roles = Role::query()->whereIn('slug', $data['roles'])->pluck('id')->all();
            $user->roles()->sync($roles);
        }

        return new UserResource($user->fresh(['department', 'roles.permissions']));
    }

    public function show(User $user): UserResource
    {
        Gate::authorize('manage_users');

        return new UserResource($user->load(['department', 'roles.permissions']));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        Gate::authorize('manage_users');

        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'department_id' => $data['department_id'] ?? null,
            'password' => empty($data['password']) ? $user->password : Hash::make($data['password']),
        ]);

        if (array_key_exists('roles', $data)) {
            $roles = Role::query()->whereIn('slug', $data['roles'] ?? [])->pluck('id')->all();
            $user->roles()->sync($roles);
        }

        return new UserResource($user->fresh(['department', 'roles.permissions']));
    }

    public function destroy(User $user)
    {
        Gate::authorize('manage_users');

        if ($user->id === auth()->id()) {
            abort(422, 'You cannot delete your own account.');
        }

        $user->delete();

        return response()->json(status: 204);
    }
}
