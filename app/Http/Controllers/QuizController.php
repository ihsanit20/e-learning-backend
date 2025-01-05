<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::query()
            ->withCount([
                'user_quizzes',
                'questions',
            ])
            ->latest()
            ->get();

        return response()->json($quizzes);
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:MCQ,Written',
            'duration' => 'nullable|integer',
            'opening_time' => 'nullable|date',
            'result_publish_time' => 'nullable|date',
        ]);

        $quiz = Quiz::create($validated_data);

        $quiz->loadCount([
            'user_quizzes',
            'questions',
        ]);

        return response()->json($quiz, 201);
    }

    public function show($id)
    {
        $quiz = Quiz::findOrFail($id);

        $quiz->load('quiz_questions.question.mcq_options');

        return response()->json($quiz);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated_data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer',
            'opening_time' => 'nullable|date',
            'result_publish_time' => 'nullable|date',
        ]);

        $quiz->update($validated_data);

        $quiz->loadCount([
            'user_quizzes',
            'questions',
        ]);

        return response()->json($quiz);
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);

        $quiz->delete();

        return response()->json(null, 204);
    }

    public function selectQuestion(Request $request, Quiz $quiz, $question_id)
    {
        $question = Question::query()
            ->when($request->category_id, function ($query, $category_id) {
                $query->whereHas('chapter.Subject', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            })
            ->find($question_id);

        if($question) {
            $max_priority = (int) (QuizQuestion::where('quiz_id', $quiz->id)->max('priority') ?? 0);

            $quiz_question = QuizQuestion::Create(
                [
                    'quiz_id' => $quiz->id,
                    'question_id' => $question->id,
                    'priority' => $max_priority + 1,
                    'mark' => $request->mark ?? ($question->type == 'MCQ' ? 1 : 10),
                    'negative_mark' => $request->negative_mark ?? 0,
                ]
            );
        }

        if($question->type == 'MCQ') {
            $quiz_question->load('question.mcq_options');
        } else {
            $quiz_question->load('question');
        }

        return response()->json([
            'quiz_question' => $quiz_question,
            'option' => 'select'
        ]);
    }

    public function removeQuestion(Request $request, Quiz $quiz, $question_id)
    {
        $response = QuizQuestion::query()
            ->where([
                'quiz_id' => $quiz->id,
                'question_id' => $question_id,
            ])
            ->delete();

        return response()->json([
            'question_id' => $response ? $question_id : null,
            'option' => 'remove'
        ]);
    }

    public function changeQuestionMark(Request $request, Quiz $quiz)
    {
        $request->validate([
            'question_ids'  => 'required|array',
            'mark'          => 'required|numeric',
            'negative_mark' => 'required|numeric',
        ]);

        $question_ids = $request->question_ids;

        $response = QuizQuestion::query()
            ->where('quiz_id', $quiz->id)
            ->whereIn('question_id', $question_ids)
            ->update([
                'mark'          => $request->mark,
                'negative_mark' => $request->negative_mark,
            ]);

        return response()->json([
            'response' => $response,
        ]);
    }

    public function results(Quiz $quiz)
    {
        $user_quizzes = $quiz->user_quizzes()
            ->with([
                'user:id,name,phone'
            ])
            ->latest('obtained_mark')
            ->where('is_practice', 0)
            ->paginate();
            // ->get();

        return response()->json(compact(
            'quiz',
            'user_quizzes',
        ));
    }
}
