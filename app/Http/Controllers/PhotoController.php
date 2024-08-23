<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'id'        => 'required|numeric',
            'option'    => 'required|string',
            'photo'     => 'required|image|max:2048', // Ensure it's an image and not larger than 2MB
        ]);

        $options = [
            'user' => User::query(),
        ];
    
        if (!isset($options[$request->option])) {
            return response()->json([
                'message' => 'Invalid option provided',
            ], 400);
        }
    
        $model_instance = $options[$request->option]->findOrFail($request->id);

        $path = $request->file('photo')->store('mentor', 's3');

        $s3Url = Storage::disk('s3')->url($path);

        $model_instance->photo = $s3Url;

        $model_instance->save();

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'path' => $s3Url
        ], 200);
    }
}
