<?php

//
$app = new \Illuminate\Container\Container();
\Illuminate\Container\Container::setInstance($app);
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);
//
$app->alias('config', \Illuminate\Contracts\Config\Repository::class);
$app->alias('config', \Illuminate\Config\Repository::class);
$app->alias('events', \Illuminate\Contracts\Events\Dispatcher::class);
$app->alias('events', \Illuminate\Events\Dispatcher::class);

//config
$swooleConfig = require __DIR__.'/../../config/config.php';


$app->singleton('config', function () use ($swooleConfig) {
    $swooleConfig['laravel']['app'] = \CrCms\Server\Tests\Laravel\Application::class;
    return new \Illuminate\Config\Repository(['swoole' => $swooleConfig]);
});

$app->instance('path.config',__DIR__.'/../../config');

//service providers
$providers = [
    \Illuminate\Events\EventServiceProvider::class,
    \CrCms\Server\Drivers\Laravel\SwooleServiceProvider::class,
];

//
$providers = array_map(function ($provider) use ($app) {
    return new $provider($app);
}, $providers);

// register
foreach ($providers as $provider) {
    $provider->register();
}

// boot
foreach ($providers as $provider) {
    if (method_exists($provider, 'boot')) {
        $provider->boot();
    }
}

return $app;