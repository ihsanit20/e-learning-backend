<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Module;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    public function index($module_id)
    {
        $lectures = Lecture::query()
            ->where('module_id', $module_id)
            ->get();

        return response()->json($lectures);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:video,virtual_platform',
            'link' => 'required|url',
            'opening_time' => 'required|date',
        ]);

        $lecture = Lecture::create($request->all());

        return response()->json($lecture, 201);
    }

    public function show($id)
    {
        $lecture = Lecture::findOrFail($id);
        return response()->json($lecture);
    }

    public function update(Request $request, Module $module, Lecture $lecture)
    {
        if($lecture->module_id != $module->id) {
            return response("Not Found!", 404);
        }

        $request->validate([
            'course_id' => 'exists:courses,id',
            'module_id' => 'exists:modules,id',
            'title' => 'string',
            'description' => 'nullable|string',
            'type' => 'in:video,virtual_platform',
            'link' => 'url',
            'opening_time' => 'date',
        ]);

        $lecture->update($request->all());

        return response()->json($lecture, 200);
    }

    public function destroy($id)
    {
        $lecture = Lecture::findOrFail($id);
        $lecture->delete();

        return response()->json(null, 204);
    }
}
