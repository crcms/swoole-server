<?php

namespace CrCms\Server\Drivers\Laravel\Commands;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\Laravel;
use CrCms\Server\Server\ServerManager;
use DomainException;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Throwable;

/**
 * Class ServerCommand.
 */
class ServerCommand  extends Command implements ApplicationContract
{
    /**
     * @var string
     */
    protected $signature = 'server {server : Configure the key of the `servers` array} {action : start or stop or restart or reload}';

    /**
     * @var array
     */
    protected $allows = ['start', 'stop', 'restart', 'reload'];

    /**
     * @var string
     */
    protected $description = 'Swoole server';

    public function handle(): void
    {
        $serverType = $this->aliasConver($this->argument('server'));

        $server = $this->getServer($serverType);
        $action = $this->argument('action');

        $server = new $server(
            $this->laravel->make('config')->get('swoole', []),
            //$this->laravel->make(ApplicationContract::class)
            new Laravel($this)
        );
//        $server->setApplication($this->laravel);

        $manager = new ServerManager($server);

        $manager->start();



        if (in_array($action, $this->allows, true)) {
            try {
                $result = call_user_func([$this, $action]);
                if ($action === 'start') {
                    return;
                }
                if ($result === false) {
                    $this->getOutput()->error("{$action} failed");
                } else {
                    $this->getOutput()->success("{$action} successfully");
                }
            } catch (Throwable $exception) {
                $this->getOutput()->error($exception->getMessage());
            }
        } else {
            $this->getOutput()->error('Allow only '.implode($this->allows, ' ').'options');
        }
    }

    /**
     * @return ServerContract
     */
    public function server(): ServerContract
    {
        $serverType = $this->aliasConver($this->argument('server'));

        //$this->cleanRunCache();

        $server = $this->getServer($serverType);

        return new $server(
            $this->getLaravel(),
            config("swoole.servers.{$serverType}"),
            'crcms.'.$serverType
        );
    }

    /**
     * @return void
     */
    protected function cleanRunCache(): void
    {
        (new Filesystem())->cleanDirectory(
            dirname($this->getLaravel()->getCachedServicesPath())
        );
    }

    public static function application(): \Illuminate\Contracts\Container\Container
    {
        $container = Container::getInstance();
        $container->bind('config',function(){
            return new \Illuminate\Config\Repository(['swoole' => require __DIR__.'/../../../../config/config.php']);
        });
        return $container;
    }

    /**
     * @param string $server
     *
     * @return string
     */
    protected function getServer(string $serverType): string
    {
        $servers = $this->laravel->make('config')->get('swoole.servers', []);

        if (in_array($serverType, array_keys($servers), true)) {
            return $this->laravel->make('config')->get("swoole.servers.{$serverType}.driver");
        }

        throw new DomainException("The server type: [{$serverType}] not found");
    }

    /**
     * @param string $serverType
     *
     * @return string
     */
    protected function aliasConver(string $serverType): string
    {
        switch ($serverType) {
            case 'ws':
                return 'websocket';
            default:
                return $serverType;
        }
    }
}
