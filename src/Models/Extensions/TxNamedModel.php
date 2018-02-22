<?php

namespace Stylers\Taxonomy\Models\Extensions;

use Illuminate\Database\Eloquent\Model;
use Stylers\Taxonomy\Models\Taxonomy;

class TxNamedModel extends Model
{
    public function name()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'name_tx_id');
    }

    public static function findByName(string $name, int $parent_id)
    {
        $taxonomy = Taxonomy::getTaxonomy($name, $parent_id);
        $model = self::where('name_tx_id', $taxonomy->id)->firstOrFail();

        return $model;
    }
}