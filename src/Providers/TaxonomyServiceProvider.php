<?php

namespace Stylers\Taxonomy\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TaxonomyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('taxonomy.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php', 'taxonomy'
        );
    }

    protected function publishDatabase()
    {
        $this->publishes([
            __DIR__ . '/../../database/Migrations/' => database_path('/migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../database/Seeders/' => database_path('/seeds')
        ], 'seeds');
    }

    protected function bootRoutes()
    {
        $this->app->booted(function () {
            require __DIR__ . '/../routes.php';
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->publishDatabase();
        $this->bootRoutes();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}