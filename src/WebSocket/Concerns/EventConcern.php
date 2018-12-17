<?php

namespace CrCms\Server\WebSocket\Concerns;

use OutOfRangeException;

/**
 * Trait EventConcern
 * @package CrCms\Server\WebSocket\Concern
 */
trait EventConcern
{
    /**
     * @var array
     */
    protected static $events = [];

    /**
     * @return array
     */
    public static function events(): array
    {
        return static::$events;
    }

    /**
     * @param string $event
     * @return bool
     */
    public static function eventExists(string $event): bool
    {
        return isset(static::$events[static::eventPrefix() . $event]);
    }

    /**
     * @param $event
     * @param $listener
     * @return EventConcern
     */
    public static function on($event, $listener): void
    {
        static::$events[static::eventPrefix() . $event] = $listener;
    }

    /**
     * @param $event
     */
    public function dispatch($event, array $data = []): void
    {
        if (!static::eventExists($event)) {
            throw new OutOfRangeException("The event[{$event}] not found");
        }

        $this->app->call(static::$events[static::eventPrefix() . $event], $data, null);
    }

    /**
     * @return string
     */
    private static function eventPrefix(): string
    {
        return 'websocket.' . (isset(static::$eventPrefix) ? static::$eventPrefix . '.' : '');
    }
}