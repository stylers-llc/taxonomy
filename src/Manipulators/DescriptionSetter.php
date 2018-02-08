<?php

namespace Stylers\Taxonomy\Manipulators;

use Stylers\Taxonomy\Models\Description;
use Stylers\Taxonomy\Models\DescriptionTranslation;
use Stylers\Taxonomy\Models\Language;

class DescriptionSetter
{

    private $translations = [];
    private $descriptionId = null;
    private $connection;

    public function __construct(array $translations = [], $descriptionId = null)
    {
        $this->translations = $translations;
        $this->descriptionId = $descriptionId;
    }

    /**
     * Set the connection associated with the model.
     * @param string $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;
        return $this;
    }

    /**
     * Creates or updates a description with its translations
     * @throws \Exception
     * @return Description
     */
    public function set()
    {
        $defaultLanguage = Language::getDefault($this->connection);
        $languageCodes = Language::getLanguageCodes($this->connection);
        $languageIdsToKeep = [];

        $description = $this->getDescription();
        if (isset($this->translations[$defaultLanguage->iso_code])
            && !is_null($this->translations[$defaultLanguage->iso_code])
        ) {
            $description->description = $this->translations[$defaultLanguage->iso_code];
        }
        if (empty($this->translations)) {
            if ($description->exists) {
                $description->delete();
                $this->descriptionId = null;
                $description = $this->getDescription();
            }
            return $description;
        }
        $description->saveOrFail();

        foreach ($this->translations as $languageCode => $translation) {
            if ($languageCode == $defaultLanguage->iso_code) {
                continue;
            }
            if (!isset($languageCodes[$languageCode])) {
                throw new \Exception("Invalid language code: `{$languageCode}`");
            }
            if (!is_null($translation)) {
                $this->setDescriptionTranslation($description->id, $languageCodes[$languageCode], $translation);
                $languageIdsToKeep[] = $languageCodes[$languageCode];
            }
        }
        $this->clearDescriptionTranslations($description->id, $languageIdsToKeep);

        $description->load('translations');
        return $description;
    }

    /**
     * Gets existing or new description
     * @return Description
     */
    private function getDescription()
    {
        $description = new Description();
        $description->setConnection($this->connection);
        if (!is_null($this->descriptionId)) {
            $description = $description->findOrFail($this->descriptionId);
        }
        return $description;
    }

    /**
     * Updates or creates a description translation
     * @param type $descriptionId
     * @param type $languageId
     * @param string $description
     * @return DescriptionTranslation
     */
    private function setDescriptionTranslation($descriptionId, $languageId, $description)
    {
        $translation = new DescriptionTranslation();
        $translation->setConnection($this->connection);

        // if translation exists, overwrite
        $matchingTranslation = $translation->where([
            'description_id' => $descriptionId,
            'language_id' => $languageId
        ])->first();
        if ($matchingTranslation) {
            $translation = $matchingTranslation;
        }

        $translation->fill([
            'description_id' => $descriptionId,
            'language_id' => $languageId,
            'description' => $description
        ])->saveOrFail();
        return $translation;
    }

    /**
     * Clears undefined translations
     * @param integer $descriptionId
     * @param array $languageIdsToKeep
     */
    private function clearDescriptionTranslations($descriptionId, $languageIdsToKeep)
    {
        $translation = new DescriptionTranslation();
        $translation->setConnection($this->connection);
        $translation->where('description_id', $descriptionId)->whereNotIn('language_id', $languageIdsToKeep)->delete();
    }

}