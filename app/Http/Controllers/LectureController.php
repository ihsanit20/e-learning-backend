<?php

namespace App\Http\Controllers;

use App\Models\CourseCompletionProgress;
use App\Models\Lecture;
use App\Models\Module;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    public function index($module_id)
    {
        $lectures = Lecture::query()
            ->where('module_id', $module_id)
            ->get();

        return response()->json($lectures);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:video,virtual_platform',
            'link' => 'required|url',
            'opening_time' => 'required|date',
        ]);

        $lecture = Lecture::create($request->all());

        return response()->json($lecture, 201);
    }

    public function show($id)
    {
        $lecture = Lecture::findOrFail($id);
        return response()->json($lecture);
    }

    public function update(Request $request, Module $module, Lecture $lecture)
    {
        if($lecture->module_id != $module->id) {
            return response("Not Found!", 404);
        }

        $request->validate([
            'course_id' => 'exists:courses,id',
            'module_id' => 'exists:modules,id',
            'title' => 'string',
            'description' => 'nullable|string',
            'type' => 'in:video,virtual_platform',
            'link' => 'url',
            'opening_time' => 'date',
            'is_completed' => 'boolean', // New validation rule for is_completed
        ]);

        $lecture->update($request->all());

        return response()->json($lecture, 200);
    }

    public function destroy($module_id, $lecture_id)
    {
        $lecture = Lecture::where('module_id', $module_id)->findOrFail($lecture_id);
        $lecture->delete();
    
        return response()->json(null, 204);
    }

    public function completeLecture(Request $request, $lectureId)
    {
        $userId = $request->user()->id;
        $courseId = $request->input('course_id');
        $isCompleted = $request->input('is_completed');
    
        $completion = CourseCompletionProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId, 'lecture_id' => $lectureId],
            ['is_completed' => $isCompleted]
        );
    
        return response()->json($completion);
    }
    
    public function getLectureCompletionStatus($userId, $lectureId)
    {
        $completion = CourseCompletionProgress::where('user_id', $userId)
                                              ->where('lecture_id', $lectureId)
                                              ->first();
    
        return response()->json(['is_completed' => $completion ? $completion->is_completed : false]);
    }
}
