<?php

namespace CrCms\Server;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Contracts\ParserContract;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use CrCms\Server\WebSocket\IO;
use CrCms\Server\WebSocket\Listeners\IOListener;
use CrCms\Server\WebSocket\Parsers\DefaultParser;
use CrCms\Server\WebSocket\Rooms\RedisRoom;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Support\ServiceProvider;

/**
 * Class WebSocketServiceProvider
 * @package CrCms\Server
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
    protected $packagePath = __DIR__ . '/../';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerEventListener();

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
            return new IO($app);
        });

        $this->app->singleton('websocket.parser', function ($app) {
            return new DefaultParser();
        });
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('websocket.io', IO::class);
        $this->app->alias('websocket.room', RoomContract::class);
        $this->app->alias('websocket.parser', ParserContract::class);
    }

    /**
     * @return void
     */
    protected function registerEventListener(): void
    {
        IO::setDispatcher($this->app['events']);
        Channel::setDispatcher($this->app['events']);
        Socket::setDispatcher($this->app['events']);

        IO::on('connection', IOListener::class . '@connection');
        IO::on('disconnection', IOListener::class . '@disconnection');
        IO::on('message', IOListener::class . '@message');
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'websocket.io',
            'websocket.room',
            'websocket.parser',
        ];
    }
}