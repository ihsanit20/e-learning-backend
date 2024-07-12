<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

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
            'materials' => 'nullable|array',
            'start_date' => 'nullable|date',
            'course_category' => 'nullable|string|max:255', // Add this line
        ]);

        $course = Course::create($validatedData);

        return response()->json($course, 201);
    }

    public function show(Course $course)
    {
        $course->load('modules.lectures'); // Eager load modules and lectures
        return response()->json($course);
    }

    public function update(Request $request, Course $course)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'price' => 'nullable|numeric',
            'materials' => 'nullable|array',
            'start_date' => 'nullable|date',
            'course_category' => 'nullable|string|max:255', // Add this line
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

    public function showPurchasedCourse(Course $course)
    {
        $course->load('modules.lectures'); // Eager load modules and lectures
        return response()->json($course);
    }
}