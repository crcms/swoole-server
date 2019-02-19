<?php

namespace CrCms\Server\Drivers\Laravel\Resetters;

use CrCms\Server\Drivers\Laravel\Contracts\ResetterContract;
use CrCms\Server\Drivers\Laravel\Laravel;
use Illuminate\Contracts\Container\Container;

class CloneResetter implements ResetterContract
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
        $baseContainer = $laravel->getBaseContainer();
        $clones = $baseContainer['config']->get('swoole.laravel.clones');

        foreach ($clones as $clone) {
            $app->instance($clone, clone $baseContainer->make($clone));
        }
    }
}