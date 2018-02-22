<?php

namespace Stylers\Taxonomy\Models\Traits;

use \BadMethodCallException;
use Stylers\Taxonomy\Models\Language;
use Stylers\Taxonomy\Models\Taxonomy;

trait TxTranslatable
{
    public function translate(string $column, string $code)
    {
//        $this->validateTranslationColumn($column);
        if (get_class($this->{$column}) !== Taxonomy::class) return null;
        $defaultCode = Language::getDefaultLanguageCode();

        if ($defaultCode == $code) $translation = $this->{$column};
        else $translation = $this->getTranslation($column, $code);

        return $translation->name;
    }

    protected function validateTranslationColumn(string $column)
    {
        if (get_class($this->{$column}) !== Taxonomy::class) {
            throw new BadMethodCallException('Not a Taxonomy: ' . $column);
        }
    }

    protected function getTranslation(string $column, string $code)
    {
        $translation = $this
            ->{$column}
            ->translations()
            ->whereHas('language', function ($query) use ($code) {
                $query->where('languages.iso_code', $code);
            })
            ->first();

        return $translation;
    }
}