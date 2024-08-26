<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Module;
use App\Models\UserMcqAnswer;
use App\Models\UserWrittenAnswer;
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
                'user_answers'  => (array) ([])
            ];
        });

        return response()->json($exam);
    }

    public function submitExamWithQuestion(Request $request, Course $course, Exam $exam)
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

        $answered_questions = $request->user_given_answers;

        // return
        $mcq_exam_questions = $exam->exam_questions()
            ->with([
                'mcqOptions:id,question_id,is_correct'
            ])
            ->get();

        foreach($answered_questions as $answered_question) {
            if(!count($answered_question['user_answers'] ?? [])) {
                continue;
            }

            if($answered_question["type"] == 'MCQ') {
                // return $this->getMark($answered_question, $mcq_exam_questions);

                UserMcqAnswer::create([
                    'exam_id'       => $exam->id,
                    'user_id'       => auth('sanctum')->id(),
                    'question_id'   => $answered_question["id"],
                    'answers'       => $answered_question["user_answers"],
                    'mark'          => $this->getMark($answered_question, $mcq_exam_questions),
                ]);
            } else {
                UserWrittenAnswer::create([
                    'exam_id'       => $exam->id,
                    'user_id'       => auth('sanctum')->id(),
                    'question_id'   => $answered_question["id"],
                    'answers'       => $answered_question["user_answers"],
                ]);
            }
        }

        return response()->json($exam);
    }

    private function getMark($answered_question, $mcq_exam_questions)
    {
        $mark = 0;

        $selected_exam_question = $mcq_exam_questions->where('question_id', $answered_question["id"])->first();

        if($selected_exam_question) {
            $is_correct = true;

            foreach($selected_exam_question->mcq_options ?? [] as $option) {
                $is_correct = in_array($option->id, $answered_question["user_answers"]) 
                    ? $is_correct
                    : !$is_correct;

                if(!$is_correct) {
                    break;
                }
            }

            $mark = $is_correct
                ? $selected_exam_question->mark
                : $selected_exam_question->negative_mark;
        }

        return $mark;
    }
}
