<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::all();
        return response()->json($galleries);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('photo')->store('ciademy/gallery', 's3');

        $gallery = new Gallery();
        $s3Url = Storage::disk('s3')->url($path);
        $gallery->photo = $s3Url;
        $gallery->save();

        return response()->json(['message' => 'Photo uploaded successfully', 'path' => $s3Url], 200);
    }

    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);

        Storage::disk('s3')->delete(parse_url($gallery->photo));

        $gallery->delete();

        return response()->json(null, 204);
    }
}
