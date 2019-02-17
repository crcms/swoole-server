<?php

namespace CrCms\Server\Server;

use CrCms\Server\Server\Contracts\ServerContract;
use DomainException;
use Swoole\Http\Server;

use Swoole\Server as SwooleServer;

class ServerFactory implements ServerContract
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $events = [
        'start' => \CrCms\Server\Server\Events\StartEvent::class,
        'worker_start' => \CrCms\Server\Server\Events\WorkerStartEvent::class,
        'close' => \CrCms\Server\Server\Events\CloseEvent::class,
        'task' => \CrCms\Server\Server\Events\TaskEvent::class,
        'finish' => \CrCms\Server\Server\Events\FinishEvent::class,
        'manager_start' => \CrCms\Server\Server\Events\ManagerStartEvent::class,
    ];

    public function create(array $config): SwooleServer
    {
        switch ($config['driver']) {
            case 'websocket':
                $server = new Server($config['host'], $config['port'], $config['mode'], $config['type']);
                break;
            case 'http':
                $server = new Server($config['host'], $config['port'], $config['mode'], $config['type']);
                break;
            case 'tcp':
                break;
            default:
                throw new DomainException("The server driver:{$config['driver']} not supported");
        }

        $server->set(array_merge($this->settings, $config['settings'] ?? []));


        return $server;
    }

    protected function mergeConfig(array $config)
    {
        $this->settings = array_merge($this->settings, $config['settings'] ?? []);
        $this->events = array_merge($this->events, $config['events'] ?? []);
    }

    public static function factory(string $driver, array $config): SwooleServer
    {
        switch ($driver) {
            case 'websocket':
                $server = new Server($config['host'], $config['port'], $config['mode'], $config['type']);
                break;
            case 'http':
                $server = new Server($config['host'], $config['port'], $config['mode'], $config['type']);
                break;
            case 'tcp':
                break;
            default:
                throw new DomainException("The server driver:{$driver} not supported");
        }

        return $server;
    }

}