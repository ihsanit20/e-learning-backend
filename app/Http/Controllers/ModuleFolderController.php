<?php

namespace App\Http\Controllers;

use App\Models\ModuleFolder;
use Illuminate\Http\Request;

class ModuleFolderController extends Controller
{
    public function index($courseId)
    {
        $folders = ModuleFolder::query()
            ->where('course_id', $courseId)
            ->with('modules')
            ->orderBy('order')
            ->get();

        return response()->json($folders, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $folder = ModuleFolder::create($validated);

        return response()->json($folder, 201);
    }

    public function show($id)
    {
        $folder = ModuleFolder::with('modules')->findOrFail($id);
        return response()->json($folder, 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $folder = ModuleFolder::findOrFail($id);
        $folder->update($validated);

        return response()->json($folder, 200);
    }

    public function destroy($id)
    {
        ModuleFolder::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
