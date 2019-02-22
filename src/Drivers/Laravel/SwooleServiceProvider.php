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

use CrCms\Server\Drivers\Laravel\Commands\ServerCommand;
use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Server\AbstractServer;
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
    protected $defer = false;

    /**
     * @var string
     */
    protected $packagePath = __DIR__.'/../../../';

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

        $this->eventListener();
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
        $this->app->alias('server', AbstractServer::class);
        $this->app->alias('server.laravel', Laravel::class);
        $this->app->alias('server.initialize.app', ApplicationContract::class);
        $this->app->alias('server.task.dispatcher', Dispatcher::class);
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('server.initialize.app', function ($app) {
            $appClass = $app['config']->get('swoole.laravel.app');
            return new $appClass($app['config']->get('swoole'));
        });

        $this->app->singleton('server.laravel', function ($app) {
            return new Laravel($app['server.initialize.app']->app());
        });

        $this->app->singleton('server.task.dispatcher', function ($app) {
            return new Dispatcher($app['server']->getServer());
        });
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
            'server',
            'server.initialize.app',
            'server.laravel',
            'server.task.dispatcher',
            ServerCommand::class,
        ];
    }
}
