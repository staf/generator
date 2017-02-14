<?php
namespace Staf\Generator;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Setup the config file for publishing.
        $this->publishes([__DIR__ . '/config.php' => config_path('generator.php')], 'config');

        // Add the default console command
        if ($this->app->runningInConsole()) {
            $this->commands([PublishCommand::class]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Builder::class, function ($app) {
            return new Builder([
                'source_path' => $app['config']['generator']['source_path'],
                'target_path' => $app['config']['generator']['source_path'],
                'cache_path'  => $app['config']['generator']['source_path'],
            ]);
        });
    }
}
