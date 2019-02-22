<?php

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Drivers\Laravel\WebSocket\Exceptions\Handler;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Laravel\Lumen\Application as LumenApplication;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * get_framework_type
 *
 * @param Container $app
 * @return string
 */
function get_framework_type(Container $app): string
{
    if ($app instanceof Application) {
        return 'Laravel';
    } elseif ($app instanceof LumenApplication) {
        return 'Lumen';
    } else {
        return 'Unknown';
    }
}

/**
 * get_framework_version
 *
 * @param Container $app
 * @return string
 */
function get_framework_version(Container $app): string
{
    $frameworkType = get_framework_type($app);
    if (in_array($frameworkType, ['Laravel', 'Lumen'], true)) {
        return strval($app->version());
    } else {
        return 'Unknown';
    }
}

/**
 * is_laravel
 *
 * @param Container $app
 * @return bool
 */
function is_laravel(Container $app): bool
{
    return get_framework_type($app) === 'Laravel';
}

/**
 * is_lumen
 *
 * @param Container $app
 * @return bool
 */
function is_lumen(Container $app): bool
{
    return get_framework_type($app) === 'Lumen';
}

/**
 * websocket_exception_report_render
 *
 * @param Container $app
 * @param \Throwable $e
 * @param Socket|null $socket
 * @return void
 */
function websocket_exception_report_render(Container $app, \Throwable $e, Socket $socket = null): void
{
    if (!$e instanceof \Exception) {
        $e = new FatalThrowableError($e);
    }

    $app->make(Handler::class)->report($e);

    if (!is_null($socket)) {
        $app->make(Handler::class)->render($socket, $e);
    }
}