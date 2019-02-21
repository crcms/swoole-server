<?php
require __DIR__ . '/../vendor/autoload.php';

//$server = new \CrCms\Server\Drivers\Base\Server(require_once __DIR__.'/../config/config.php');
//
//$serverManager = new \CrCms\Server\Server\ServerManager($server);
//
//if ($argv[1] === 'start') {
//    $serverManager->start();
//} else {
//    $serverManager->reload();
//}


$container = new \Illuminate\Container\Container();
$container->singleton('config',function(){
    return new \Illuminate\Config\Repository(['swoole' => require __DIR__.'/../config/config.php']);
});

$container->make('config')->set(['swoole.laravel.app' => \CrCms\Server\Tests\Laravel\Application::class]);

$event = new \Illuminate\Events\Dispatcher($container);

$application = new \Illuminate\Console\Application(
    $container,$event,'5.7'

);


$providers = [
    \CrCms\Server\Drivers\Laravel\SwooleServiceProvider::class,
];
$providerObjects = [];
foreach ($providers as $provider) {
    $providerObject = new $provider($container);
    $providerObject->register();
}
foreach ($providerObjects as $providerObject) {
    $providerObject->boot();
}

//
//$application = new \Symfony\Component\Console\Application();
////
$command = new \CrCms\Server\Drivers\Laravel\Commands\ServerCommand();

$application->add($command);

$application->run();

//$command->handle();
//dd($command);
//$manage = new \CrCms\Server\Server\ServerManager();
//
//$manage->run($command,new \CrCms\Server\Http\Server());