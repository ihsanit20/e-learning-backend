<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Course;

class PurchaseController extends Controller
{
    public function purchaseCourse(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = $request->user();
        $course = Course::find($validated['course_id']);

        // Check if the user has already purchased the course
        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'You have already purchased this course'], 400);
        }

        // Create the purchase
        Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json(['message' => 'Course purchased successfully'], 201);
    }

    public function getPurchasedCourses(Request $request)
    {
        $user = $request->user();
        $courses = $user->courses()->with('modules.lectures')->get();
        return response()->json($courses);
    }
}