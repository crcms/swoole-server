<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\Http\Server as HttpServer;
use CrCms\Server\WebSocket\Events\CloseEvent;
use CrCms\Server\WebSocket\Events\MessageEvent;
use CrCms\Server\WebSocket\Events\OpenEvent;
use Swoole\WebSocket\Server as WebSocketServer;
use CrCms\Server\Http\Events\RequestEvent;

/**
 * Class Server
 * @package CrCms\Server\WebSocket
 */
class Server extends HttpServer
{
    /**
     * @var array
     */
    protected $events = [
        'request' => RequestEvent::class,
        'open' => OpenEvent::class,
        'message' => MessageEvent::class,
        'close' => CloseEvent::class,
    ];

    /**
     * @return void
     */
    public function createServer(): void
    {
        $serverParams = [
            $this->config['host'],
            $this->config['port'],
            $this->config['mode'] ?? SWOOLE_PROCESS,
            $this->config['type'] ?? SWOOLE_SOCK_TCP,
        ];

        $this->server = new WebSocketServer(...$serverParams);
        $this->setPidFile();
        $this->setSettings($this->config['settings'] ?? []);
        $this->eventDispatcher($this->config['events'] ?? []);
    }
}