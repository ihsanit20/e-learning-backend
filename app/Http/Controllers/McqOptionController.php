<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\McqOption;

class McqOptionController extends Controller
{
    public function index()
    {
        return McqOption::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'required|string|max:255',
            'is_correct' => 'required|boolean',
        ]);

        $mcqOption = McqOption::create($validatedData);
        return response()->json($mcqOption, 201);
    }

    public function show(McqOption $mcqOption)
    {
        return $mcqOption;
    }

    public function update(Request $request, McqOption $mcqOption)
    {
        $validatedData = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'required|string|max:255',
            'is_correct' => 'required|boolean',
        ]);

        $mcqOption->update($validatedData);
        return response()->json($mcqOption, 200);
    }

    public function destroy(McqOption $mcqOption)
    {
        $mcqOption->delete();
        return response()->json(null, 204);
    }
}
