<?php

namespace CrCms\Server\WebSocket\Concerns;

use Illuminate\Contracts\Events\Dispatcher;

/**
 * Trait EventConcern
 * @package CrCms\Server\WebSocket\Concern
 */
trait EventConcern
{
    /**
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public static function setDispatcher(Dispatcher $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * @param $event
     * @param $listener
     * @return EventConcern
     */
    public static function on($event, $listener): void
    {
        static::$dispatcher->listen(static::eventPrefix() . $event, $listener);
    }

    /**
     * @param $event
     */
    public function dispatch($event, array $data = []): void
    {
        static::$dispatcher->dispatch(static::eventPrefix() . $event, [$this, $data]);
    }

    /**
     * @return string
     */
    private static function eventPrefix(): string
    {
        return 'websocket.' . (isset(static::$eventPrefix) ? static::$eventPrefix . '.' : '');
    }
}