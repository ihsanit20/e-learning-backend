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
            ->where('module_id', $module_id)
            ->get();

        return response()->json($exams);
    }

    public function store(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'duration' => 'required|integer',
            'opening_time' => 'nullable|date',
            'link' => 'nullable|string|max:255',
        ]);

        $exam = Exam::create($request->all());
        return response()->json($exam, 201);
    }

    public function show($id)
    {
        $exam = Exam::findOrFail($id);

        $exam->load('questions.mcqOptions');

        return response()->json($exam);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer',
            'opening_time' => 'nullable|date',
            'link' => 'nullable|string|max:255',
        ]);

        $exam = Exam::findOrFail($id);
        $exam->update($request->all());
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
            ->with('mcqOptions')
            ->when($request->category_id, function ($query, $category_id) {
                $query->whereHas('chapter.Subject', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            })
            ->find($question_id);

        if($question) {
            $max_priority = (int) (ExamQuestion::where('exam_id', $exam->id)->max('priority') ?? 0);

            ExamQuestion::Create([
                'exam_id' => $exam->id,
                'question_id' => $question->id,
                'priority' => $max_priority + 1 ,
            ]);
        }

        return response()->json([
            'question' => $question,
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
}

