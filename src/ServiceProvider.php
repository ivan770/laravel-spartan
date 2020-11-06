<?php

namespace Ivan770\Spartan;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider as BaseProvider;

class ServiceProvider extends BaseProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/spartan.php', 'queue.connections.spartan');

        $this->app
            ->when(SpartanConnector::class)
            ->needs(ClientInterface::class)
            ->give(function () {
                return new Client([
                    'base_uri' => $this->app
                        ->make(Repository::class)
                        ->get('queue.connections.spartan.host')
                ]);
            });
    }

    public function boot()
    {
        $this->app
            ->make(QueueManager::class)
            ->addConnector('spartan', function () {
                return $this->app->make(SpartanConnector::class);
            });
    }
}