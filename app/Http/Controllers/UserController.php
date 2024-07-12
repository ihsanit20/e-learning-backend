<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'photo' => 'nullable|string',
            'role' => 'required|string|in:developer,admin,instructor,student', // Validate role
        ]);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
            'photo' => 'nullable|string',
            'role' => 'required|string|in:developer,admin,instructor,student', // Validate role
        ]);

        $user->update($validated);

        return response()->json($user);
    }
    
    public function getUsers()
    {
        $users = User::all();
        return response()->json($users);
    }
}
