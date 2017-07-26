<?php

namespace Stylers\Taxonomy\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stylers\Taxonomy\Models\Taxonomy;

class TaxonomyController extends Controller
{

    public function store(Request $request)
    {
        $taxonomy = new Taxonomy($request->all());
        $success = $taxonomy->save();
        return ['success' => $success, 'data' => $taxonomy->attributesToArray()];
    }

    public function update(Request $request, $id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        $taxonomy->fill($request->except(['id']));
        $success = $taxonomy->save();
        return ['success' => $success, 'data' => $taxonomy->attributesToArray()];
    }

    public function show(Request $request, $id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->attributesToArray()];
    }

    public function destroy(Request $request, $id)
    {
        $count = Taxonomy::destroy($id);
        return ['success' => (bool)$count, 'data' => Taxonomy::withTrashed()->findOrFail($id)];
    }

    public function index(Request $request)
    {
        return ['success' => true, 'data' => Taxonomy::all()];
    }

    public function getDescendants($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->getDescendants()];
    }

    public function getChildren($id = null)
    {
        if (is_null($id)) {
            return ['success' => true, 'data' => Taxonomy::getRoots()];
        }
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->getChildren()];
    }

    public function getLeaves($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->getLeaves()];
    }

    public function getAncestors($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->getAncestors()];
    }

    public function getAncestorsAndSelf($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->getAncestorsAndSelf()];
    }

    public function getSiblings($id)
    {
        $taxonomy = Taxonomy::findOrFail($id);
        return ['success' => true, 'data' => $taxonomy->getSiblings()];
    }

    public function setPriorities(Request $request)
    {
        $taxonomyIds = $request->get('taxonomy_ids');
        $i = 1;
        foreach ($taxonomyIds as $taxonomyId) {
            $taxonomy = Taxonomy::findOrFail($taxonomyId);
            $taxonomy->priority = $i++;
            $taxonomy->save();
        }
        return [
            'success' => true,
            'data' => Taxonomy::whereIn('id',
                $taxonomyIds)->orderBy((new Taxonomy())->getQualifiedOrderColumnName())->get()
        ];
    }

}