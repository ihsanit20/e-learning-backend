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
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
            'photo' => 'nullable|string',
            'role' => 'required|string|in:developer,admin,instructor,student',
            'affiliate_status' => 'nullable|in:Pending,Active,Inactive',
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
        $path = $request->file('photo')->store('ciademy/user', 's3');

        $s3Url = Storage::disk('s3')->url($path);

        $user->photo = $s3Url;
        $user->save();

        return response()->json(['message' => 'Photo uploaded successfully', 'path' => $s3Url], 200);
    }

    public function applyAffiliate(Request $request)
    {
        // return $request->additional_info;
        
        $user = $request->user();

        $user->update([
            'affiliate_status'  => 'Pending',
            'additional_info'   => $request->additional_info,
        ]);

        return $user;
    }
}
