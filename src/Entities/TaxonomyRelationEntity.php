<?php

namespace Stylers\Taxonomy\Entities;


use Stylers\Taxonomy\Models\TaxonomyRelation;

class TaxonomyRelationEntity
{

    protected $relation;

    public function __construct()
    {
    }

    public function getFrontendData(int $txId) : array
    {
        $return = [];
        $txRelations = TaxonomyRelation::getRelationsOfTaxonomy($txId);
        foreach ($txRelations as $txRelation) {
            $return[$txRelation->right->name] = (new TaxonomyEntity($txRelation->right))->getFrontendData(['translations']);
        }
        return $return;
    }
}