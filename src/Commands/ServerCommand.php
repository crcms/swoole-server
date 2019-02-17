<?php

namespace CrCms\Server\Commands;

use CrCms\Server\AbstractServerCommand;
use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\Http\Server;
use CrCms\Server\Server\Contracts\ServerContract;
use CrCms\Server\Server\ServerManager;
use DomainException;
use Illuminate\Filesystem\Filesystem;
use Throwable;

/**
 * Class ServerCommand.
 */
class ServerCommand extends AbstractServerCommand
{
    /**
     * @var string
     */
    protected $signature = 'server {server : Configure the key of the `servers` array} {action : start or stop or restart or reload}';

    protected $allows = ['start', 'stop', 'restart']; //, 'reload'

    /**
     * @var string
     */
    protected $description = 'Swoole server';

    public function handle(): void
    {
        dd($this->arguments());
        $action = $this->argument('action');

        $server = new Server(
            $this->laravel->make('config')->get('swoole',[]),
            $this->app()
        );
//        $server->setApplication($this->laravel);

        $manager = new ServerManager($server);

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

    protected function app()
    {
        return $this->laravel;
    }

    /**
     * @param string $server
     *
     * @return string
     */
    protected function getServer(string $serverType): string
    {
        if (in_array($serverType, array_keys(config('swoole.servers')), true)) {
            return config("swoole.servers.{$serverType}.driver");
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
