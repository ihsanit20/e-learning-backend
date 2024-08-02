<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index()
    {
        return response()->json(Chapter::with('questions')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $chapter = Chapter::create($validated);

        return response()->json($chapter, 201);
    }

    public function show($id)
    {
        $chapter = Chapter::with('questions')->findOrFail($id);

        return response()->json($chapter);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'subject_id' => 'sometimes|exists:subjects,id',
        ]);

        $chapter = Chapter::findOrFail($id);
        $chapter->update($validated);

        return response()->json($chapter);
    }

    public function destroy($id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return response()->json(null, 204);
    }
}
