<?php

namespace App\Http\Controllers;

use App\Models\McqOption;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\UserMcqAnswer;
use App\Models\UserQuiz;
use App\Models\UserQuizMcqAnswer;
use App\Models\UserQuizWrittenAnswer;
use App\Models\UserWrittenAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserQuizController extends Controller
{
    public function index(Request $request)
    {
        $quizzes = Quiz::query()
            ->has('questions')
            ->where('duration', '>', 0)
            ->whereNotNull('opening_time')
            ->when(request()->limit, function ($query, $limit) {
                return $query->take($limit);
            })
            ->latest('opening_time')
            ->get();

        return response()->json($quizzes);
    }

    public function show(Request $request, Quiz $quiz)
    {
        Question::$quiz_id = $quiz->id;

        $quiz->loadCount('quiz_questions');

        $quiz->load([
            'user_quiz' => function ($query) {
                $query->withCount([
                    'user_quiz_mcq_answers',
                    'user_quiz_written_answers',
                ]);
            }
        ]);
        
        $has_user_quiz = !!$quiz->user_quiz;

        if($has_user_quiz && !$quiz->is_practice) {
            if($quiz->user_quiz->obtained_mark == null) {
                $quiz->user_quiz()->update([
                    'obtained_mark' => ($quiz->user_quiz->mcq_correct_mark ?? 0) 
                        - ($quiz->user_quiz->mcq_negative_mark ?? 0) 
                        + ($quiz->user_quiz->written_mark ?? 0)
                ]);
            }

            $upper_position = $quiz->user_quizzes()
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
                'user_quiz_mcq_answer',
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
                'user_answers'  => (array) ($question->user_quiz_mcq_answer->answers ?? []),
            ];
        });        

        return response()->json($quiz);
    }

    public function fetchQuizWithQuestion(Quiz $quiz)
    {
        Question::$quiz_id = $quiz->id;
        
        if (Carbon::now()->lessThan(Carbon::make($quiz->opening_time))) {
            return response()->json([
                'message' => 'Quiz is not yet open.'
            ], 403);
        }

        $questions = $quiz->questions()
            ->with([
                'mcq_options:id,question_id,option_text,is_correct',
                'user_quiz_mcq_answer',
            ])
            ->get();
        
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

            $upper_position = $quiz->user_quizzes()
                ->where('obtained_mark', '>', $quiz->user_quiz->obtained_mark)
                ->where('is_practice', 0)
                ->latest('obtained_mark')
                ->count();

            $quiz->user_quiz->position = $upper_position + 1;
        }

        $quiz->has_user_quiz = $has_user_quiz;
        
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
                'user_answers'  => (array) ($question->user_quiz_mcq_answer->answers ?? []),
            ];
        });        

        return response()->json($quiz);
    }

    public function submitQuizWithQuestion(Request $request, Quiz $quiz)
    {
        if (Carbon::now()->lessThan(Carbon::make($quiz->opening_time))) {
            return response()->json([
                'message' => 'Quiz is not yet open.'
            ], 403);
        }

        $answered_questions = $request->user_given_answers;

        // return
        $mcq_quiz_questions = $quiz->quiz_questions()
            ->get();

        $mcq_correct_mark = 0;
        $mcq_negative_mark = 0;

        $user_id = auth('sanctum')->id();

        $user_quiz = UserQuiz::create([
            'quiz_id'       => $quiz->id,
            'user_id'       => $user_id,
            'is_practice'   => Carbon::now() > Carbon::make($quiz->result_publish_time),
        ]);

        foreach($answered_questions as $answered_question) {
            if(!count($answered_question['user_answers'] ?? [])) {
                continue;
            }

            if($answered_question["type"] == 'MCQ') {
                $mark = $this->getMark($answered_question, $mcq_quiz_questions, $quiz);

                if ($mark > 0) {
                    $mcq_correct_mark += $mark;
                } elseif ($mark < 0) {
                    $mcq_negative_mark += (- $mark);
                }

                UserQuizMcqAnswer::create([
                    'user_quiz_id'  => $user_quiz->id,
                    'question_id'   => $answered_question["id"],
                    'answers'       => $answered_question["user_answers"],
                    'mark'          => $mark,
                ]);
            } else {
                UserQuizWrittenAnswer::create([
                    'user_quiz_id'  => $user_quiz->id,
                    'question_id'   => $answered_question["id"],
                    'answers'       => $answered_question["user_answers"],
                ]);
            }
        }
        
        $user_quiz->update([
            'mcq_correct_mark'  => $mcq_correct_mark,
            'mcq_negative_mark' => $mcq_negative_mark,
            'obtained_mark'     => $mcq_correct_mark - $mcq_negative_mark,
        ]);

        $quiz->user_quiz = $user_quiz;

        return response()->json($quiz);
    }

    private function getMark($answered_question, $mcq_quiz_questions, $quiz)
    {
        $mark = 0;

        $selected_quiz_question = $mcq_quiz_questions->where('question_id', $answered_question["id"])->first();

        if(!$selected_quiz_question) {
            $selected_quiz_question = QuizQuestion::query()
                ->where([
                    'quiz_id'       => $quiz->id,
                    'question_id'   => $answered_question["id"],
                ])
                ->first();
        }

        $correct_option_ids = McqOption::query()
            ->where('question_id', $answered_question["id"])
            ->where('is_correct', 1)
            ->pluck('id')
            ->toArray();

        $user_selected_option_ids = $answered_question["user_answers"];

        $mark = (!array_diff($correct_option_ids, $user_selected_option_ids) && !array_diff($user_selected_option_ids, $correct_option_ids))
            ? $selected_quiz_question->mark
            : - $selected_quiz_question->negative_mark;

        return $mark;
    }
}
