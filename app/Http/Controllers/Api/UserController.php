<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = User::with(['role', 'manager']);

        if ($request->has('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $newUser = User::create($validated);

        return response()->json($newUser->load(['role', 'manager']), 201);
    }

    public function show(User $user)
    {
        $currentUser = request()->user();

        if (!$currentUser->canManageUsers() && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->load(['role', 'manager', 'teamMembers.role']);

        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (!$currentUser->canManageUsers() && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'sometimes|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Only admins can change roles
        if (isset($validated['role_id']) && !$currentUser->canManageUsers()) {
            unset($validated['role_id']);
        }

        $user->update($validated);

        return response()->json($user->load(['role', 'manager']));
    }

    public function destroy(User $user)
    {
        $currentUser = request()->user();

        if (!$currentUser->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'Cannot delete your own account'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}

