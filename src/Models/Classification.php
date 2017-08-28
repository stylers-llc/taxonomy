<?php

namespace Stylers\Taxonomy\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{

    use SoftDeletes,
        ModelValidatorTrait;

    public function classificationTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    public function value()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'value_taxonomy_id');
    }

    public function priceTaxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'price_taxonomy_id');
    }

    public function additionalDescription()
    {
        return $this->hasOne(Description::class, 'id', 'additional_description_id');
    }
}