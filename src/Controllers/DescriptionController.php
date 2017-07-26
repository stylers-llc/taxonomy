<?php

namespace Stylers\Taxonomy\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stylers\Taxonomy\Models\Description;

class DescriptionController extends Controller
{
    public function store(Request $request)
    {
        $description = new Description($request->all());
        $success = $description->save();
        return ['success' => $success, 'data' => $description->attributesToArray()];
    }

    public function update(Request $request, $id)
    {
        $description = Description::findOrFail($id);
        $description->fill($request->except(['id']));
        $success = $description->save();
        return ['success' => $success, 'data' => $description->attributesToArray()];
    }

    public function show(Request $request, $id)
    {
        $description = Description::findOrFail($id);
        return ['success' => true, 'data' => $description->attributesToArray()];
    }

    public function destroy(Request $request, $id)
    {
        $count = Description::destroy($id);
        return ['success' => (bool)$count, 'data' => Description::withTrashed()->findOrFail($id)];
    }

    public function index(Request $request)
    {
        return ['success' => true, 'data' => Description::all()];
    }

}