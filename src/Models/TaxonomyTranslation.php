<?php

namespace Stylers\Taxonomy\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TaxonomyTranslation extends Model
{

    use SoftDeletes,
        ModelValidatorTrait;

    protected $fillable = ['language_id', 'taxonomy_id', 'name'];

    public function language()
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }

    public function taxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    static public function getTaxonomyTranslation($taxonomyId, $languageId, $database = null)
    {
        $txTr = new TaxonomyTranslation();
        $txTr->setConnection($database);
        return $txTr->where(['taxonomy_id' => $taxonomyId, 'language_id' => $languageId])->firstOrFail();
    }

    /**
     * @param int|null $parentId
     * @param string $name
     * @param int|null $id
     * @return bool
     */
    public static function isUnique(int $parentId = null, string $name, int $id = null): bool
    {
        $hasTranslations = (bool)self::whereHas('taxonomy', function ($query) use ($parentId) {
            $query->where('parent_id', $parentId);
        })->where('name', $name)->where('id', '!=', $id)->count();

        return !$hasTranslations;
    }
}
