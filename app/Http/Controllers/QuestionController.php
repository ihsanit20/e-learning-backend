<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return response()->json(Question::with(['mcqOptions', 'writtenAnswers'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'type' => 'required|in:MCQ,Written',
            'question_text' => 'required|string',
        ]);

        $question = Question::create($validated);

        return response()->json($question, 201);
    }

    public function show($id)
    {
        $question = Question::with(['mcqOptions', 'writtenAnswers'])->findOrFail($id);

        return response()->json($question);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'chapter_id' => 'sometimes|exists:chapters,id',
            'type' => 'sometimes|in:MCQ,Written',
            'question_text' => 'sometimes|string',
        ]);

        $question = Question::findOrFail($id);
        $question->update($validated);

        return response()->json($question);
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json(null, 204);
    }
}
