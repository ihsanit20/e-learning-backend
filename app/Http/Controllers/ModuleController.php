<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index($courseId)
    {
        $modules = Module::query()
            ->where('course_id', $courseId)
            ->with('folder')
            ->orderBy('order')
            ->get();

        return response()->json($modules, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_folder_id' => 'nullable|exists:module_folders,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'prerequisite_module_id' => 'nullable|exists:modules,id',
        ]);

        $module = Module::create($validated);

        return response()->json($module->load('folder'), 201);
    }

    public function show($id)
    {
        return Module::with('course', 'prerequisite', 'folder')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'module_folder_id' => 'nullable|exists:module_folders,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'prerequisite_module_id' => 'nullable|exists:modules,id',
        ]);

        $module = Module::findOrFail($id);
        $module->update($validated);
        return response()->json($module->load('folder'), 200);
    }

    public function destroy($id)
    {
        Module::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    public function assignModuleFolder(Request $request, $id)
    {
        $validated = $request->validate([
            'module_folder_id' => 'nullable|exists:module_folders,id',
        ]);

        $module = Module::findOrFail($id);
        $module->update($validated);
        return response()->json($module->load('folder'), 200);
    }
}
