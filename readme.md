###Install component
1. add Baum\Providers\BaumServiceProvider::class to config/app providers
2. add Stylers\Taxonomy\Providers\TaxonomyServiceProvider::class to config/app providers
3. php artisan vendor:publish --provider="Stylers\Taxonomy\Providers\TaxonomyServiceProvider"
4. php artisan db:seed --class=TaxonomyDatabaseSeeder
