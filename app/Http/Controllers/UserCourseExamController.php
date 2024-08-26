<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserCourseExamController extends Controller
{
    public function fetchExamWithQuestion(Course $course, Exam $exam)
    {
        if (Carbon::now()->lessThan(Carbon::make($exam->opening_time))) {
            return response()->json([
                'message' => 'Exam is not yet open.'
            ], 403);
        }

        Module::query()
            ->where([
                'id' => $exam->module_id,
                'course_id' => $course->id,
            ])
            ->firstOrFail();

        $questions = $exam->questions()
            ->with([
                'mcqOptions:id,question_id,option_text'
            ])
            ->get();

        $exam->questions = $questions->map(function ($question) {
            return [
                'id'            => $question->id,
                'type'          => $question->type,
                'question_text' => $question->question_text,
                'mcq_options'   => $question->mcqOptions,
            ];
        });

        return response()->json($exam);
    }
}
