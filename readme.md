###Install component

1. add Stylers\Taxonomy\Providers\TaxonomyServiceProvider::class to config/app providers
2. php artisan vendor:publish --provider="Stylers\Taxonomy\Providers\TaxonomyServiceProvider"
3. php artisan db:seed --class=TaxonomyDatabaseSeeder
