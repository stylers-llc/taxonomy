<?php

namespace Stylers\Taxonomy\Models\Traits;

use \BadMethodCallException;
use Stylers\Taxonomy\Models\Language;
use Stylers\Taxonomy\Models\Taxonomy;

trait TxTranslatable
{
    public function translate(string $column, string $code = null)
    {
//        $this->validateTranslationColumn($column);      
        if (is_null($this->{$column}) || get_class($this->{$column}) !== Taxonomy::class) {
            return null;
        }

        $defaultCode = Language::getDefaultLanguageCode();
        if (is_null($code)) {
            $code = $defaultCode;
        }

        if ($defaultCode == $code) {
            $translation = $this->{$column};
        } else {
            $translation = $this->getTranslation($column, $code);
        }

        return $translation ? $translation->name : null;
    }

    protected function validateTranslationColumn(string $column)
    {
        if (is_null($this->{$column}) || get_class($this->{$column}) !== Taxonomy::class) {
            throw new BadMethodCallException('Not a Taxonomy: ' . $column);
        }
    }

    protected function getTranslation(string $column, string $code)
    {
        if(is_null($this->{$column})) {
            return null;
        }
            
        $translation = $this
            ->{$column}
            ->translations()
            ->whereHas('language', function ($query) use ($code) {
                $query->where('languages.iso_code', $code);
            })
            ->first();
        return $translation;
    }

//    TODO
    public function translateOption(string $column, string $code = null)
    {
//        $this->validateTranslationColumn($column);
        if (is_null($this->{$column}) || get_class($this->{$column}) !== Taxonomy::class) return null;

        $defaultCode = Language::getDefaultLanguageCode();
        if (is_null($code)) $code = $defaultCode;

        $translation = $this->getTranslation($column, $code);

        return $translation ? $translation->name : null;
    }
}
