<?php

namespace CrCms\Server\Drivers\Laravel\Contracts;

use CrCms\Server\Drivers\Laravel\Laravel;
use Illuminate\Contracts\Container\Container;

interface ResetterContract
{
    /**
     * handle
     *
     * @param Container $app
     * @param Laravel $laravel
     * @return void
     */
    public function handle(Container $app, Laravel $laravel): void;
}