<?php

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use Illuminate\Contracts\Container\Container;

class Application implements ApplicationContract
{
    /**
     * initialization application
     *
     * @return Container
     */
    public static function app(): Container
    {
        return require base_path('bootstrap/app.php');
    }
}