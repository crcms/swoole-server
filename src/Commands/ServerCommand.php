<?php

namespace CrCms\Server\Commands;

use CrCms\Server\AbstractServerCommand;
use CrCms\Server\Server\Contracts\ServerContract;
use DomainException;
use Illuminate\Filesystem\Filesystem;

/**
 * Class ServerCommand.
 */
class ServerCommand extends AbstractServerCommand
{
    /**
     * @var string
     */
    protected $signature = 'server {server : http or websocket} {action : start or stop or restart}';

    /**
     * @var string
     */
    protected $description = 'Swoole server';

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
