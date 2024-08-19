<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'price' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'category_id' => 'required',
            'course_type' => 'required|string|in:Live Course,Recorded Course', // Validation for course_type
        ]);

        $course = Course::create($validatedData);

        return response()->json($course, 201);
    }

    public function show(Course $course)
    {
        $course->load([
            'modules.lectures',
            'modules.exams',
        ]);

        $course->is_purchased = false;

        if($user = Request()->user('sanctum')) {
            $course->is_purchased = Purchase::query()
                ->where([
                    'user_id'   => $user->id,
                    'course_id' => $course->id,
                ])
                ->exists();
        }

        return response()->json($course);
    }

    public function update(Request $request, Course $course)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'price' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'category_id' => 'required',
            'course_type' => 'required|string|in:Live Course,Recorded Course', // Validation for course_type
        ]);

        $course->update($validatedData);

        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(null, 204);
    }

    public function latest()
    {
        $courses = Course::orderBy('created_at', 'desc')->take(3)->get();
        return response()->json($courses);
    }

    public function coursesByCategory($categoryName)
    {
        $category = Category::where('name', $categoryName)->firstOrFail();
        $courses = Course::where('category_id', $category->id)->get();
        return response()->json($courses);
    }

    public function showPurchasedCourse(Course $course)
    {
        $course->load('modules.lectures'); // Eager load modules and lectures
        return response()->json($course);
    }

    public function uploadThumbnail(Request $request, Course $course)
    {
        $request->validate([
            'thumbnail' => 'required|image|max:2048',
        ]);

        $path = $request->file('thumbnail')->store('ciademy/courses', 's3');

        // Get the full URL of the uploaded file
        $s3Url = Storage::disk('s3')->url($path);

        // Save the full URL to the course's thumbnail attribute
        $course->thumbnail = $s3Url;
        $course->save();

        return response()->json(['message' => 'Thumbnail uploaded successfully', 'path' => $s3Url], 200);
    }
}
