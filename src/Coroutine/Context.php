<?php

namespace CrCms\Server\Coroutine;

use Swoole\Coroutine;

class Context
{
    /**
     * @var array
     */
    protected static $context = [];

    /**
     * Get current coroutine value by key
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get(string $key)
    {
        return static::$context[static::getCoroutineId()][$key] ?? null;
    }

    /**
     * Put current coroutine value
     *
     * @param string $key
     * @param $value
     * @return void
     */
    public static function put(string $key, $value): void
    {
        static::$context[static::getCoroutineId()][$key] = $value;
    }

    /**
     * Delete current coroutine value by key
     *
     * @param string $key
     * @return void
     */
    public static function delete(string $key): void
    {
        unset(static::$context[static::getCoroutineId()][$key]);
    }

    /**
     * clearAll
     *
     * @return void
     */
    public static function clearAll(): void
    {
        static::$context = [];
    }

    /**
     * getCurrentCoroutineId
     *
     * @return int
     */
    public static function getCoroutineId(): int
    {
        return Coroutine::getuid();
    }
}