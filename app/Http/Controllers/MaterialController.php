<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Module;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index($module_id)
    {
        $materials = Material::query()
            ->where('module_id', $module_id)
            ->get();

        return response()->json($materials);
    }

    public function store(Request $request)
    {
        $material = Material::create(
            $this->getValidatedData($request)
        );

        return response()->json($material, 201);
    }

    public function show($id)
    {
        $material = Material::findOrFail($id);

        return response()->json($material);
    }

    public function update(Request $request, Module $module, Material $material)
    {
        if($material->module_id != $module->id) {
            return response("Not Found!", 404);
        }

        $material->update(
            $this->getValidatedData($request, $material->id)
        );

        return response()->json($material, 200);
    }

    public function destroy($module_id, $material_id)
    {
        $material = Material::query()
            ->where('module_id', $module_id)
            ->findOrFail($material_id);

        $material->delete();
    
        return response()->json(null, 204);
    }

    private function getValidatedData($request, $id = '')
    {
        return $request->validate([
            'module_id'     => 'required|exists:modules,id',
            'title'         => 'required|string',
            'description'   => 'nullable|string',
            'opening_time'  => 'required|date',
            'link'          => 'required|url',
        ]);
    }
}
