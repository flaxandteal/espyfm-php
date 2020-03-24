<?php

namespace Flaxandteal\EspyFM;

use Cviebrock\LaravelElasticsearch\Manager as Elasticsearch;
use GuzzleHttp;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;

use Flaxandteal\EspyFM\EspyFMService;
use Flaxandteal\EspyFM\IsRecommendedItem;
use Flaxandteal\EspyFM\ItemObserver;

class EspyFMServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../../config/espyfm.php' => config_path('espyfm.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../../config/espyfm.php', 'espyfm');

        $this->app->singleton(EspyFMService::class, function ($app) {
            $client = $this->app->make(GuzzleHttp\Client::class);
            $config = $this->app->make(Repository::class);
            $itemClass = $config->get('espyfm.item-class', IsRecommendedItem::class);
            $userClass = $config->get('espyfm.user-class', \App\User::class);
            $baseUrl = $config->get('espyfm.api-base-url', 'http://localhost:5000');
            return new EspyFMService($client, $baseUrl, $itemClass, $userClass);
        });

        $this->app->singleton(ItemObserver::class, function ($app) {
            $config = $this->app->make(Repository::class);
            $client = $this->app->make(Elasticsearch::class);
            $itemClass = $config->get('espyfm.item-class', IsRecommendedItem::class);
            return new ItemObserver($client, $itemClass);
        });
    }
}
