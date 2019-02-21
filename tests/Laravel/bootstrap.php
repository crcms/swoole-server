<?php

$app = new \Illuminate\Container\Container();
\Illuminate\Container\Container::setInstance($app);
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);

$app->alias('config', \Illuminate\Contracts\Config\Repository::class);
$app->alias('config', \Illuminate\Config\Repository::class);
$app->alias('events', \Illuminate\Contracts\Events\Dispatcher::class);
$app->alias('events', \Illuminate\Events\Dispatcher::class);

//config
$swooleConfig = __DIR__.'/../../config/config.php';
$app->singleton('config', function () use ($swooleConfig) {
    return new \Illuminate\Config\Repository(['swoole' => $swooleConfig]);
});
//function config_path($path = null)
//{
//    return is_null($path) ? __DIR__ : __DIR__.'/'.$path;
//}
//function app_path($path = null)
//{
//    return is_null($path) ? __DIR__ : __DIR__.'/'.$path;
//}
//function resource_path($path = null)
//{
//    return is_null($path) ? __DIR__ : __DIR__.'/'.$path;
//}
//function app() {
//    return \Illuminate\Container\Container::getInstance();
//}
//function config($key,$default = null)
//{
//    \Illuminate\Container\Container::getInstance()->make('config')->get($key,$default);
//}
//function trans($key = null, $replace = [], $locale = null)
//{
//
//    return \Illuminate\Container\Container::getInstance()->make('translator')->trans($key, $replace, $locale);
//}
//$request = Mockery::mock('request');
//$request->shouldReceive('all')->andReturn([]);
//$app->instance('request',$request);
//service providers
$providers = [
    \Illuminate\Events\EventServiceProvider::class,
    \CrCms\Server\Drivers\Laravel\SwooleServiceProvider::class,
];

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