<?php

namespace CrCms\Server\Drivers\Laravel\Facades;

use CrCms\Server\Server\Contracts\TaskContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static false|int|string dispatch(TaskContract $task, array $params = [], bool $async = true, float $timeout = 1)
 *
 * Class Dispatcher
 */
class Dispatcher extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'server.task.dispatcher';
    }
}
