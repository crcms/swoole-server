<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:26
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Commands\ServerCommand;
use CrCms\Server\Http\Listeners\RequestHandledListener;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\ServerActionContract;
use CrCms\Server\Server\Contracts\ServerContract;
use CrCms\Server\Server\Tasks\Dispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Class ServerServiceProvider.
 */
class SwooleServiceProvider extends ServiceProvider
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
     * @var string
     */
    protected $name = 'swoole';

    /**
     * @return void
     */
    public function boot(): void
    {
        $publishs = [
            $this->packagePath.'config/config.php' => config_path($this->name.'.php'),
        ];
        if ($this->app['config']->get('swoole.enable_websocket', false)) {
            $publishs[$this->packagePath.'routes/websocket.php'] = base_path('routes/websocket.php');
        }
        $this->publishes($publishs);

        $this->registerEventListener();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->packagePath.'config/config.php', $this->name
        );

        $this->registerServerAlias();

        $this->registerCommands();

        $this->registerServices();

        if ($this->app['config']->get('swoole.enable_websocket', false)) {
            $this->app->register(WebSocketServiceProvider::class);
        }
    }

    /**
     * @return void
     */
    protected function registerCommands(): void
    {
        //require swoole-server
        $this->commands(ServerCommand::class);
    }

    /**
     * @return void
     */
    protected function registerServerAlias(): void
    {
        foreach ([
                     AbstractServer::class,
                     ServerActionContract::class,
                     ServerContract::class,
                 ] as $alias) {
            $this->app->alias('server', $alias);
        }

        $this->app->alias('server.task.dispatcher', Dispatcher::class);
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('server.task.dispatcher', function ($app) {
            return new Dispatcher($app['server']->getServer());
        });
    }

    /**
     * @return void
     */
    protected function registerEventListener(): void
    {
        foreach ($this->app['config']->get('swoole.reload_provider_events', []) as $event) {
            $this->app['events']->listen($event, RequestHandledListener::class);
        }
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            'server',
            'server.task.dispatcher',
            ServerCommand::class,
        ];
    }
}
