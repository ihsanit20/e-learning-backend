<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BundleController extends Controller
{
    const WITH = [
        'bundleCourses:id,bundle_id,course_id,course_price',
        'bundleCourses.course:id,title,price',
    ];

    public function index()
    {
        $bundles = Bundle::query()
            ->with(self::WITH)
            ->get();

        return response()->json($bundles);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $bundle = Bundle::create($validatedData);

        $this->saveBundleCourses($bundle, $request);

        $bundle->load(self::WITH);

        return response()->json($bundle, 201);
    }

    public function show(Request $request, Bundle $bundle)
    {
        $bundle->load(self::WITH);

        $authUser = $request->user('sanctum');

        $bundle->purchased_course_ids = $authUser
            ? $authUser->courses()
                ->whereIn('course_id', $bundle->bundleCourses->pluck('course_id'))
                ->pluck('course_id')
            : [];

        return response()->json($bundle);
    }

    public function update(Request $request, Bundle $bundle)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $bundle->update($validatedData);

        $this->saveBundleCourses($bundle, $request);

        $bundle->load(self::WITH);

        return response()->json($bundle);
    }

    public function updateActiveStatus(Request $request, Bundle $bundle)
    {
        $validatedData = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $bundle->update($validatedData);

        $bundle->load(self::WITH);

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

    protected function saveBundleCourses($bundle, $request)
    {
        if ($request->has('bundle_courses')) {
            $request->validate([
                'bundle_courses' => 'array',
                'bundle_courses.*.course_id' => 'required|exists:courses,id',
                'bundle_courses.*.course_price' => 'required|numeric|min:0',
            ]);

            $bundle->bundleCourses()->delete();

            foreach ($request->bundle_courses as $bundle_course) {
                $bundle->bundleCourses()->create([
                    'course_id' => $bundle_course['course_id'],
                    'course_price' => $bundle_course['course_price'],
                ]);
            }
        }
    }
}
