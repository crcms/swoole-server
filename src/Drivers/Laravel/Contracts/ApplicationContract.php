<?php

namespace CrCms\Server\Drivers\Laravel\Contracts;

use Illuminate\Contracts\Container\Container;

interface ApplicationContract
{
    public static function application(): Container;
}