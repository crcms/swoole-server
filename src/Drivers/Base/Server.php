<?php

namespace CrCms\Server\Drivers\Base;

use CrCms\Server\Server\Contracts\ServerContract;
use CrCms\Server\Server\EventDispatcher;

/**
 *
 */
class Server implements ServerContract
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * name
     *
     * @return string
     */
    public function name(): string
    {
        return 'server';
    }

    /**
     * create
     *
     * @return void
     */
    public function create(): void
    {
        $serverParams = [
            $this->config['host'],
            $this->config['port'],
            $this->config['mode'] ?? SWOOLE_PROCESS,
            $this->config['type'] ?? SWOOLE_SOCK_TCP,
        ];

        $this->server = new HttpServer(...$serverParams);
        $this->setPidFile();
        $this->setSettings($this->config['settings'] ?? []);
        $this->eventDispatcher($this->config['events'] ?? []);
        EventDispatcher::dispatch()
    }


}