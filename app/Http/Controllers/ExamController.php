<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index($moduleId)
    {
        $exams = Exam::where('module_id', $moduleId)->get();
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
}

