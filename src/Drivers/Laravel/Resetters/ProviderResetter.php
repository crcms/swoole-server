<?php

namespace CrCms\Server\Drivers\Laravel\Resetters;

use CrCms\Server\Drivers\Laravel\Contracts\ResetterContract;
use CrCms\Server\Drivers\Laravel\Laravel;
use Illuminate\Contracts\Container\Container;

class ProviderResetter implements ResetterContract
{
    /**
     * handle
     *
     * @param Container $app
     * @param Laravel $laravel
     * @return void
     */
    public function handle(Container $app, Laravel $laravel): void
    {
        $providers = $laravel->getConfig()->get('swoole.laravel.providers');

        foreach ($providers as $provider) {
            $app->register($provider, true);
            $provider = $app->getProvider($provider);
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }
}