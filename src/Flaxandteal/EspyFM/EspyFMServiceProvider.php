<?php

namespace Flaxandteal\EspyFM;

use GuzzleHttp;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;

use Flaxandteal\EspyFM\EspyFMService;

class EspyFMServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../../config/espyfm.php' => config_path('espyfm.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/espyfm.php', 'espyfm');

        $this->app->singleton(EspyFMService::class, function ($app, GuzzleHttp\Client $client, Repository $config) {
            $baseUrl = $config->get('espyfm.api-base-url', 'http://localhost:5000');
            return new EspyFMService($client, $baseUrl);
        });
    }
}
