<?php

namespace CrCms\Server\Server\Facades;

use CrCms\Server\Server\Contracts\TaskContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static false|int|string dispatch(TaskContract $task, array $params = [], bool $async = true)
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
