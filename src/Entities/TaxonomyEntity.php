<?php

namespace Stylers\Taxonomy\Entities;


use Stylers\Taxonomy\Models\Language;
use Stylers\Taxonomy\Models\Taxonomy;

class TaxonomyEntity
{
    protected $taxonomy;

    public function __construct(Taxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function getAdminData(array $additions = [], array $dependencies = [])
    {
        if (is_null($this->taxonomy)) {
            return null;
        }

        $return = [
            'id' => $this->taxonomy->id,
            'parent_id' => $this->taxonomy->parent_id,
            'name' => $this->taxonomy->name,
            'priority' => $this->taxonomy->priority,
            'is_active' => $this->taxonomy->is_active,
            'is_required' => $this->taxonomy->is_required,
            'is_merchantable' => $this->taxonomy->is_merchantable,
            'type' => $this->taxonomy->type,
            'icon' => $this->taxonomy->icon
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'translations':
                    $return['translations'] = $this->getTranslations();
                    break;
                case 'descendants':
                    $return['descendants'] = $this->getDescendants($additions, $dependencies);
                    break;
                case 'relation':
                    $dependencies[] = $this->taxonomy;
                    $relation = $this->taxonomy->getTaxonomyRelation($dependencies);
                    $return['relation'] = $relation ? $relation->getFrontendData() : null;
                    break;
            }
        }

        return $return;
    }

    public function getFrontendData(array $additions = [])
    {
        if (is_null($this->taxonomy)) {
            return null;
        }

        $return = [
            'id' => $this->taxonomy->id,
            'name' => $this->taxonomy->name,
            'priority' => $this->taxonomy->priority,
            'is_required' => $this->taxonomy->is_required,
            'type' => $this->taxonomy->type,
            'icon' => $this->taxonomy->icon
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'translations':
                    $return['translations'] = $this->getTranslations();
                    break;
            }
        }

        return $return;
    }

    public function translations()
    {
        $taxonomies = [Language::getDefault()->iso_code => $this->taxonomy->name];
        return array_merge($taxonomies, $this->getTranslations());
    }

    private function getTranslations()
    {
        $return = [];
        $translations = $this->taxonomy->translations;
        foreach ($translations as $translation) {
            $return[$translation->language->iso_code] = $translation->name;
        }
        return $return;
    }

    private function getDescendants(array $additions = [], array $dependencies = [])
    {
        $return = [];
        $childTaxonomies = $this->taxonomy->getChildren()->sortBy('priority')->values()->all();
        foreach ($childTaxonomies as $childTaxonomy) {
            $return[] = (new self($childTaxonomy))->getAdminData($additions, $dependencies);
        }
        return $return;
    }

    static public function getCollection($taxonomies, array $additions = [], array $dependencies = [])
    {
        $return = [];
        foreach ($taxonomies as $taxonomy) {
            $return[] = (new self($taxonomy))->getAdminData($additions, $dependencies);
        }
        return $return;
    }
}