<?php

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Drivers\Laravel\WebSocket\Channel;
use CrCms\Server\WebSocket\Contracts\ConverterContract;
use CrCms\Server\WebSocket\Contracts\ParserContract;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use CrCms\Server\WebSocket\Exceptions\Handler;
use CrCms\Server\WebSocket\IO;
use CrCms\Server\WebSocket\Rooms\RedisRoom;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Class WebSocketServiceProvider.
 */
class WebSocketServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @var string
     */
    protected $packagePath = __DIR__.'/../';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->eventListener();

        $this->loadRoute();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAlias();

        $this->registerServices();
    }

    /**
     * @return void
     */
    protected function loadRoute(): void
    {
        $webSocketPath = base_path('routes/websocket.php');
        if (file_exists($webSocketPath)) {
            require $webSocketPath;
        }
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('websocket.room', function ($app) {
            return new RedisRoom($app['redis']->connection('websocket'));
        });

        $this->app->singleton('websocket.io', function ($app) {
            $io = new IO($app['websocket.room']);
            $channels = $app['config']->get('swoole.websocket_channels', ['/']);
            foreach ($channels as $channel) {
                $io->addChannel(new Channel($app, $io, $channel));
            }

            return $io;
        });

        $this->app->singleton('websocket.parser', function ($app) {
            $parser = $app['config']->get('swoole.websocket_parser');

            return new $parser($app);
        });

        $this->app->singleton('websocket.data_converter', function ($app) {
            $converter = $app['config']->get('swoole.websocket_data_converter');

            return new $converter($app);
        });

        //$this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('websocket', Socket::class);
        $this->app->alias('websocket.io', IO::class);
        $this->app->alias('websocket.room', RoomContract::class);
        $this->app->alias('websocket.parser', ParserContract::class);
        $this->app->alias('websocket.data_converter', ConverterContract::class);
    }

    /**
     * @return void
     */
    protected function eventListener(): void
    {

    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'websocket',
            'websocket.io',
            'websocket.room',
            'websocket.parser',
            'websocket.data_converter',
        ];
    }
}
