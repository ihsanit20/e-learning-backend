<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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
            'role' => 'required|string|in:developer,admin,instructor,student',
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
            'role' => 'required|string|in:developer,admin,instructor,student',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function getUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $user = $request->user();
        $path = $request->file('photo')->store('ciademy', 's3');

        // Get the full URL of the uploaded file
        $s3Url = Storage::disk('s3')->url($path);

        // Save the full URL to the user's photo attribute
        $user->photo = $s3Url;
        $user->save();

        return response()->json(['message' => 'Photo uploaded successfully', 'path' => $s3Url], 200);
    }
}
