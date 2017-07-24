<?php

namespace Stylers\Taxonomy\Manipulators;


use Stylers\Taxonomy\Models\Language;
use Stylers\Taxonomy\Models\Taxonomy;
use Stylers\Taxonomy\Models\TaxonomyTranslation;

class TaxonomySetter
{
    private $translations = [];
    private $taxonomyId = null;

    private $connection;

    public function __construct(array $translations, $taxonomyId = null, $parentTaxonomyId = null)
    {
        $this->translations = $translations;
        $this->taxonomyId = $taxonomyId;
        $this->parentTaxonomyId = $parentTaxonomyId;
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
     * Creates or updates a taxonomy with its translations
     * @throws \Exception
     * @return Taxonomy
     */
    public function set()
    {
        $defaultLanguage = Language::getDefault($this->connection);
        $languageCodes = Language::getLanguageCodes($this->connection);
        $languageIdsToKeep = [];

        if (!isset($this->translations[$defaultLanguage->iso_code])) {
            throw new \Exception("Missing translation for main language (`{$defaultLanguage->iso_code}`)");
        }

        $taxonomy = $this->getTaxonomy();
        $taxonomy->name = $this->translations[$defaultLanguage->iso_code];
        $taxonomy->saveOrFail();

        unset($this->translations[$defaultLanguage->iso_code]);
        if (empty($this->translations)) {
            return $taxonomy;
        }

        foreach ($this->translations as $languageCode => $translation) {
            if (!isset($languageCodes[$languageCode])) {
                throw new \Exception("Invalid language code: `{$languageCode}`");
            }
            $this->setTaxonomyTranslation($taxonomy->id, $languageCodes[$languageCode], $translation);
            $languageIdsToKeep[] = $languageCodes[$languageCode];
        }
        $this->clearTaxonomyTranslations($taxonomy->id, $languageIdsToKeep);

        $taxonomy->load('translations');
        return $taxonomy;
    }

    /**
     * Gets existing or new taxonomy
     * @return Taxonomy
     */
    private function getTaxonomy()
    {
        $taxonomy = new Taxonomy();
        $taxonomy->setConnection($this->connection);
        if (!is_null($this->taxonomyId)) {
            $taxonomy = $taxonomy->findOrFail($this->taxonomyId);
        } else {
            $taxonomy->parent_id = $this->parentTaxonomyId;
        }
        return $taxonomy;
    }

    /**
     * Updates or creates a taxonomy translation
     * @param type $taxonomyId
     * @param type $languageId
     * @param string $translationName
     * @return TaxonomyTranslation
     */
    private function setTaxonomyTranslation($taxonomyId, $languageId, $translationName)
    {
        $translation = new TaxonomyTranslation();
        $translation->setConnection($this->connection);

        // if translation exists, overwrite
        $matchingTranslation = $translation->where([
            'taxonomy_id' => $taxonomyId,
            'language_id' => $languageId
        ])->first();
        if ($matchingTranslation) {
            $translation = $matchingTranslation;
        }

        $translation->fill([
            'taxonomy_id' => $taxonomyId,
            'language_id' => $languageId,
            'name' => $translationName
        ])->saveOrFail();

        return $translation;
    }

    /**
     * Clears undefined translations
     * @param integer $taxonomyId
     * @param array $languageIdsToKeep
     */
    private function clearTaxonomyTranslations($taxonomyId, $languageIdsToKeep)
    {
        $translation = new TaxonomyTranslation();
        $translation->setConnection($this->connection);
        $translation->where('taxonomy_id', $taxonomyId)->whereNotIn('language_id', $languageIdsToKeep)->delete();
    }
}