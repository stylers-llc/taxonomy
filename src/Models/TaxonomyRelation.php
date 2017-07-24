<?php

namespace Stylers\Taxonomy\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxonomyRelation extends Model
{
    use SoftDeletes,
        ModelValidatorTrait;

    protected $fillable = ['type_taxonomy_id', 'lft_taxonomy_id', 'rgt_taxonomy_id', 'priority'];

    public function left()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'lft_taxonomy_id');
    }

    public function right()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'rgt_taxonomy_id');
    }

    public function type()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    public function additionalDescription()
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }

    public static function getRelationsOfTaxonomy(int $txId, int $typeTxId = null) : \Illuminate\Database\Eloquent\Collection
    {
        $tmp = (new self())->where('lft_taxonomy_id', '=', $txId);
        if($typeTxId)
        {
            $tmp->where('type_taxonomy_id', '=', $typeTxId);
        }
        return $tmp->orderBy('priority')->get();
    }

    public static function getRelationNamesOfTaxonomy(int $txId, int $typeTxId): array
    {
        $return = [];
        foreach (self::getRelationsOfTaxonomy($txId, $typeTxId) as $txRelation) {
            $return[] = $txRelation->right->name;
        }
        return $return;
    }
}