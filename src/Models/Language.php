<?php

namespace Stylers\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Language extends Model {

    use SoftDeletes,
        ModelValidatorTrait;

    protected $fillable = ['name_taxonomy_id', 'iso_code', 'date_format', 'time_format', 'first_day_of_week'];

    public function name() {
        return $this->hasOne(Taxonomy::class, 'id', 'name_taxonomy_id');
    }

    static public function getLanguageCodes($database = null) {
        $connection = is_null($database) ? DB::connection() : DB::connection($database);
        return $connection->table('languages')->pluck('id', 'iso_code');
    }

    private static $defaultLanguage = null;
    private static $defaultLanguageDatabase = null;

    /**
     * Returns default language object
     * @return Language
     */
    static public function getDefault($database = null) {
        if (is_null(self::$defaultLanguage) || self::$defaultLanguageDatabase != $database) {
            $language = new Language();
            $language->setConnection($database);
            self::$defaultLanguage = $language->findOrFail(Config::get('taxonomy.languages.' . Config::get('taxonomy.default_language'))['language_id']);
        }
        return self::$defaultLanguage;
    }

    static public function getByCode($isoCode, $database = null) {
        return Language::on($database)->where('iso_code', '=', $isoCode)->firstOrFail();
    }

    public static function getByName($name)
    {
        $tx = Taxonomy::getTaxonomy(trim($name), Config::get('taxonomy.language'));
        return self::where('name_taxonomy_id', '=', $tx->id)->firstOrFail();
    }

    public static function getOptions()
    {
        $languages = self::all();

        $options = [];
        foreach ($languages as $language) {
            $temp = [];
            $temp['iso_code'] = $language->iso_code;
            $temp['value'] = $language->name->name;
            $temp['translations']['en'] = $language->name->name;
            $translations = $language->name->translations;
            foreach ($translations as $translation) {
                $key = Language::findOrFail($translation->language_id)->iso_code;
                $temp['translations'][$key] = $translation->name;
            }
            $options[] = $temp;
        }
        return $options;
    }

    static public function getDefaultLanguageCode() {
        return DB::table('languages')->where('is_default', 1)->value('iso_code');
    }

    static public function getDefaultLanguageId() {
        return DB::table('languages')->where('is_default', 1)->value('id');
    }

    static public function getDefaultLanguage() {
        return DB::table('languages')->where('is_default', 1)->first();
    }
}
