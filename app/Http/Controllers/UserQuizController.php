<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;

class UserQuizController extends Controller
{
    public function index(Request $request)
    {
        $quizzes = Quiz::query()
            ->get();

        return response()->json($quizzes);
    }

    public function show(Request $request, Quiz $quiz)
    {
        $quiz->load('user_quiz');
        
        $has_user_quiz = !!$quiz->user_quiz;

        if($has_user_quiz && !$quiz->is_practice) {
            if($quiz->user_quiz->obtained_mark == null) {
                $quiz->user_quiz()->update([
                    'obtained_mark' => ($quiz->user_quiz->mcq_correct_mark ?? 0) 
                        - ($quiz->user_quiz->mcq_negative_mark ?? 0) 
                        + ($quiz->user_quiz->written_mark ?? 0)
                ]);
            }

            $upper_position = $quiz->user_quizs()
                ->where('obtained_mark', '>', $quiz->user_quiz->obtained_mark)
                ->where('is_practice', 0)
                ->latest('obtained_mark')
                ->count();

            $quiz->user_quiz->position = $upper_position + 1;
        }

        $quiz->has_user_quiz = $has_user_quiz;

        $questions = $quiz->questions()
            ->with([
                'mcq_options:id,question_id,option_text,is_correct',
                'user_mcq_answer',
            ])
            ->get();
        
        $quiz->questions = $questions->map(function ($question) use ($has_user_quiz) {
            // Determine if `is_correct` should be included in the options
            $mcqOptions = $question->mcq_options->map(function ($option) use ($has_user_quiz) {
                return [
                    'id'           => $option->id,
                    'question_id'  => $option->question_id,
                    'option_text'  => $option->option_text,
                    'is_correct'   => $has_user_quiz ? $option->is_correct : null, // Include `is_correct` only if there's a user_quiz
                ];
            });
        
            return [
                'id'            => $question->id,
                'type'          => $question->type,
                'question_text' => $question->question_text,
                'mcq_options'   => $mcqOptions,
                'user_answers'  => (array) ($question->user_mcq_answer->answers ?? []),
            ];
        });        

        return response()->json($quiz);
    }
}
