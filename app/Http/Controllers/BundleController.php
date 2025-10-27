<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::query()
            ->with([
                'bundleCourses.course',
            ])
            ->get();

        return response()->json($bundles);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
        ]);

        $bundle = Bundle::create($validatedData);

        $bundle->load('bundleCourses.course');

        return response()->json($bundle, 201);
    }

    public function show(Bundle $bundle)
    {
        $bundle->load([
            'bundleCourses.course',
        ]);

        return response()->json($bundle);
    }

    public function update(Request $request, Bundle $bundle)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
        ]);

        $bundle->update($validatedData);

        $bundle->load('bundleCourses.course');

        return response()->json($bundle);
    }

    public function updateActiveStatus(Request $request, Bundle $bundle)
    {
        $validatedData = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $bundle->update($validatedData);

        $bundle->load('bundleCourses.course');

        return response()->json($bundle);
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();

        return response()->json(null, 204);
    }

    public function uploadThumbnail(Request $request, Bundle $bundle)
    {
        $request->validate([
            'thumbnail' => 'required|image|max:2048',
        ]);

        $path = $request->file('thumbnail')->store('ciademy/bundles', 's3');

        // Get the full URL of the uploaded file
        $s3Url = Storage::disk('s3')->url($path);

        // Save the full URL to the bundle's thumbnail attribute
        $bundle->thumbnail = $s3Url;
        $bundle->save();

        return response()->json(['message' => 'Thumbnail uploaded successfully', 'path' => $s3Url], 200);
    }
}
