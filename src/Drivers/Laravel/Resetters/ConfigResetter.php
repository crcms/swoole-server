<?php

namespace CrCms\Server\Drivers\Laravel\Resetters;

use CrCms\Server\Drivers\Laravel\Contracts\ResetterContract;
use CrCms\Server\Drivers\Laravel\Laravel;
use Illuminate\Contracts\Container\Container;

class ConfigResetter implements ResetterContract
{
    /**
     * Clone config
     * Running in the coroutine, if you do not re-clone, if you modify the config under high concurrency, the data of other coroutines will be confused.
     * So you must ensure that there is only one config per coroutine.
     *
     *
     * @param Container $app
     * @param Laravel $laravel
     * @return void
     */
    public function handle(Container $app, Laravel $laravel): void
    {
        $app->instance('config', clone $laravel->getConfig());
    }
}