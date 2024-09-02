<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseCompletionProgress;
use App\Models\Purchase;
use App\Models\UserExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::query()
            ->with([
                'category',
            ])
            ->get();

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
        $userId = auth('sanctum')->id();

        $course->load([
            'modules.lectures',
            'modules.exams' => function ($query) {
                $query->withCount('exam_questions');
            },
            'modules.exams.user_exam' => function ($query) {
                $query->withCount('user_mcq_answers');
            },
        ]);  

        // Get all lecture and exam IDs
        $course_lecture_ids = $course->modules->flatMap(fn($module) => $module->lectures->pluck('id'));
        $course_exam_ids = $course->modules->flatMap(fn($module) => $module->exams->pluck('id'));

        // Get IDs of completed lectures and exams
        $completed_lecture_ids = CourseCompletionProgress::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $course_lecture_ids)
            ->pluck('lecture_id')
            ->toArray();

        $completed_exam_ids = UserExam::query()
            ->where('user_id', $userId)
            ->whereIn('exam_id', $course_exam_ids)
            ->pluck('exam_id')
            ->toArray();

        $count_complete_lecture = 0;
        $count_complete_exam = 0;

        // Add is_complete to each lecture and exam
        foreach ($course->modules as $module) {
            foreach ($module->lectures as $lecture) {
                $lecture->is_completed = in_array($lecture->id, $completed_lecture_ids);
                $count_complete_lecture += ($lecture->is_completed ? 1 : 0);
            }

            foreach ($module->exams as $exam) {
                $exam->is_completed = in_array($exam->id, $completed_exam_ids);
                $count_complete_exam += ($exam->is_completed ? 1 : 0);
            }
        }

        $total_contents = count($course_lecture_ids) + count($course_exam_ids);
        $total_complete_contents = $count_complete_lecture + $count_complete_exam;

        // Add properties to the course object
        $course->total_contents = $total_contents;
        $course->total_complete_contents = $total_complete_contents;

        // Calculate and assign progress
        $course->progress = round($total_contents > 0 ? ($total_complete_contents / $total_contents) * 100 : 0);

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
