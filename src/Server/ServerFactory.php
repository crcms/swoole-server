<?php

namespace CrCms\Server\Server;

use DomainException;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Server as SwooleServer;

class ServerFactory
{
    /**
     * Create swoole server
     *
     * @param array $config
     * @return HttpServer|SwooleServer|WebSocketServer
     */
    public static function factory(array $config): SwooleServer
    {
        $driver = $config['driver'] ?? 'http';
        $mode = $config['mode'] ?? SWOOLE_PROCESS;
        $type = $config['type'] ?? SWOOLE_SOCK_TCP;

        switch ($driver) {
            case 'http':
                $server = new HttpServer($config['host'], $config['port'], $mode, $type);
                break;
            case 'websocket':
                $server = new WebSocketServer($config['host'], $config['port'], $mode, $type);
                break;
            case 'tcp':
                $server = new SwooleServer($config['host'], $config['port'], $mode, $type);
                break;
            default:
                throw new DomainException("The server driver:{$driver} not supported");
        }

        return $server;
    }
}