<?php

namespace Stylers\Taxonomy\Entities;


use Stylers\Taxonomy\Models\Description;
use Stylers\Taxonomy\Models\Language;

class DescriptionEntity
{
    protected $description;

    public function __construct(Description $description)
    {
        $this->description = $description;
    }

    public function getFrontendData()
    {
        if (is_null($this->description)) {
            return null;
        }

        $return = [Language::getDefault()->iso_code => $this->description->description];
        $translations = $this->description->translations;
        foreach ($translations as $translation) {
            $return[$translation->language->iso_code] = $translation->description;
        }
        return $return;
    }

    static public function getCollection(array $descriptions)
    {
        $return = [];
        foreach ($descriptions as $description) {
            $return[] = (new self($description))->getFrontendData();
        }
        return $return;
    }
}