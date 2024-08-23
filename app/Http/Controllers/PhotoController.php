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

        $directoryPath = $request->option . '/' . date('Y') . '/' . $model_instance->id;

        $filename = uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();

        $path = $request->file('photo')->storeAs($directoryPath, $filename, 's3');

        $s3Url = Storage::disk('s3')->url($path);
        
        $old_photo_url = $model_instance->photo;
        
        $model_instance->photo = $s3Url;

        $model_instance->save();

        if ($old_photo_url) {
            $old_photo_path = parse_url($old_photo_url, PHP_URL_PATH);
            $old_photo_path = ltrim($old_photo_path, '/');
    
            if (Storage::disk('s3')->exists($old_photo_path)) {
                Storage::disk('s3')->delete($old_photo_path);
            }
        }

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'path' => $s3Url,
        ], 200);
    }
}
