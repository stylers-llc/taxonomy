<?php

namespace Stylers\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Description extends Model
{

    use SoftDeletes,
        ModelValidatorTrait;

    protected $fillable = ['description'];

    public function translations()
    {
        return $this->hasMany(DescriptionTranslation::class);
    }

}
