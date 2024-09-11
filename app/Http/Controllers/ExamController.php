<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index($module_id)
    {
        $exams = Exam::query()
            ->withCount('user_exams')
            ->where('module_id', $module_id)
            ->get();

        return response()->json($exams);
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:MCQ,Written',
            'duration' => 'required|integer',
            'opening_time' => 'nullable|date',
            'result_publish_time' => 'nullable|date',
            'link' => 'nullable|string|max:255',
        ]);

        $exam = Exam::create($validated_data);

        return response()->json($exam, 201);
    }

    public function show($id)
    {
        $exam = Exam::findOrFail($id);

        $exam->load('exam_questions.question.mcq_options');

        return response()->json($exam);
    }

    public function update(Request $request, Exam $exam)
    {
        $validated_data = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer',
            'opening_time' => 'nullable|date',
            'result_publish_time' => 'nullable|date',
            'link' => 'nullable|string|max:255',
        ]);

        $exam->update($validated_data);

        return response()->json($exam);
    }

    public function destroy($module_id, $id)
    {
        $exam = Exam::where('module_id', $module_id)
        ->findOrFail($id);
        $exam->delete();
        return response()->json(null, 204);
    }

    public function selectQuestion(Request $request, Exam $exam, $question_id)
    {
        $question = Question::query()
            ->when($request->category_id, function ($query, $category_id) {
                $query->whereHas('chapter.Subject', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            })
            ->find($question_id);

        if($question) {
            $max_priority = (int) (ExamQuestion::where('exam_id', $exam->id)->max('priority') ?? 0);

            $exam_question = ExamQuestion::Create(
                [
                    'exam_id' => $exam->id,
                    'question_id' => $question->id,
                    'priority' => $max_priority + 1,
                    'mark' => $request->mark ?? ($question->type == 'MCQ' ? 1 : 10),
                    'negative_mark' => $request->negative_mark ?? 0,
                ]
            );
        }

        if($question->type == 'MCQ') {
            $exam_question->load('question.mcq_options');
        } else {
            $exam_question->load('question');
        }

        return response()->json([
            'exam_question' => $exam_question,
            'option' => 'select'
        ]);
    }

    public function removeQuestion(Request $request, Exam $exam, $question_id)
    {
        $response = ExamQuestion::query()
            ->where([
                'exam_id' => $exam->id,
                'question_id' => $question_id,
            ])
            ->delete();

        return response()->json([
            'question_id' => $response ? $question_id : null,
            'option' => 'remove'
        ]);
    }

    public function changeQuestionMark(Request $request, Exam $exam)
    {
        $request->validate([
            'question_ids'  => 'required|array',
            'mark'          => 'required|numeric',
            'negative_mark' => 'required|numeric',
        ]);

        $question_ids = $request->question_ids;

        $response = ExamQuestion::query()
            ->where('exam_id', $exam->id)
            ->whereIn('question_id', $question_ids)
            ->update([
                'mark'          => $request->mark,
                'negative_mark' => $request->negative_mark,
            ]);

        return response()->json([
            'response' => $response,
        ]);
    }

    public function results(Exam $exam)
    {
        $user_exams = $exam->user_exams()
            ->with([
                'user:id,name,phone'
            ])
            ->latest('obtained_mark')
            ->where('is_practice', 0)
            ->paginate();
            // ->get();

        return response()->json(compact(
            'exam',
            'user_exams',
        ));
    }
}

