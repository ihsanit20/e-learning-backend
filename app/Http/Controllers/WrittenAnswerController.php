<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WrittenAnswer;

class WrittenAnswerController extends Controller
{
    public function index()
    {
        return WrittenAnswer::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'student_id' => 'required|exists:users,id',
            'answer_image_path' => 'required|string|max:255',
        ]);

        $writtenAnswer = WrittenAnswer::create($validatedData);
        return response()->json($writtenAnswer, 201);
    }

    public function show(WrittenAnswer $writtenAnswer)
    {
        return $writtenAnswer;
    }

    public function update(Request $request, WrittenAnswer $writtenAnswer)
    {
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'student_id' => 'required|exists:users,id',
            'answer_image_path' => 'required|string|max:255',
        ]);

        $writtenAnswer->update($validatedData);
        return response()->json($writtenAnswer, 200);
    }

    public function destroy(WrittenAnswer $writtenAnswer)
    {
        $writtenAnswer->delete();
        return response()->json(null, 204);
    }
}
