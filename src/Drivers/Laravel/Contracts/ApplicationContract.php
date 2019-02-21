<?php

namespace CrCms\Server\Drivers\Laravel\Contracts;

use Illuminate\Contracts\Container\Container;

interface ApplicationContract
{
    /**
     * A laravel or lumen application
     *
     * @return Container
     */
    public function app(): Container;
}