<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Stylers\Taxonomy\Models\Taxonomy;
use Stylers\Taxonomy\Models\Language;
use Stylers\Taxonomy\Models\TaxonomyTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaxonomyDatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->seedRelations();
        $this->seedLanguages();
    }

    protected function seedRelations()
    {
        $languageTx = Taxonomy::loadTaxonomy(Config::get('taxonomy.relation'));
        $languageTx->name = 'relation';
        $languageTx->save();

        foreach (Config::get('taxonomy.relations') as $name => $id) {
            $tx = Taxonomy::loadTaxonomy($id);
            $tx->name = $name;
            $tx->save();
            $tx->makeChildOf($languageTx);
        }
    }

    protected function seedLanguages()
    {
        $languageTx = Taxonomy::loadTaxonomy(Config::get('taxonomy.language'));
        $languageTx->name = 'language';
        $languageTx->save();

        foreach (Config::get('taxonomy.languages') as $name => $properties) {
            $tx = Taxonomy::loadTaxonomy($properties['id']);
            $tx->name = $name;
            $tx->save();
            $tx->makeChildOf($languageTx);

            try {
                $language = Language::findOrFail($properties['language_id']);
            } catch (ModelNotFoundException $e) {
                $language = new Language();
                $language->id = $properties['language_id'];
            }
            $language->name_taxonomy_id = $properties['id'];
            $language->iso_code = $properties['iso_code'];
            $language->culture_name = $properties['culture_name'];
            $language->date_format = $properties['date_format'];
            $language->time_format = $properties['time_format'];
            $language->first_day_of_week = $properties['first_day_of_week'];

            if (Config::get('taxonomy.default_language') == $name) {
                $language->is_default = 1;
            }
            $language->save();
        }
        foreach (Config::get('taxonomy.languages') as $name => $properties) {
            $tx = Taxonomy::loadTaxonomy($properties['id']);
            $this->createTranslations($properties, $tx);
        }
    }

    private function createTranslations($data, Taxonomy $tx)
    {
        if (!empty($data['translations'])) {
            foreach ($data['translations'] as $lang_code => $tr_name) {
                $language = Language::getByCode($lang_code);
                $tr = new TaxonomyTranslation();
                $tr->language_id = $language->id;
                $tr->taxonomy_id = $tx->id;
                $tr->name = $tr_name;
                $tr->save();
            }
        }
    }
}
