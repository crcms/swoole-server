<?php

namespace CrCms\Server\WebSocket\Listeners;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\IO;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Pipeline\Pipeline;
use Swoole\Http\Request;

/**
 * Class IOListener
 * @package CrCms\Server\WebSocket\Listeners
 */
class IOListener
{
    /**
     * @param IO $io
     * @param array $data ['request' => Swoole\Http\Request, 'app' => Application]
     */
    public function connection(IO $io, array $data)
    {
        $io->setCurrentChannel(
            $this->findOrCreateChannel($io, $data['request'])
        );
    }

    /**
     * @param IO $io
     * @param array $data ['app' => Application,'request' => Swoole\Http\Request, 'frame' => Swoole\WebSocket\Frame]
     */
    public function message(IO $io, array $data)
    {
        //解析数据
        $frame = $data['app']->make('websocket.parser')->unpack($data['frame']);

        //
        $socket = new Socket(
            $data['app'],
            $data['request'],
            $io->getCurrentChannel(),
            $data['frame'],
            $frame
        );

        //中间件调度
        (new Pipeline($data['app']))
            ->send($socket)
            ->through(config('swoole.websocket_middleware'))
            ->then(function (Socket $socket) {
                //event dispatch 触发事件
                $frame = $socket->getFrame();
                $socket->dispatch($frame['event'], $frame['data']);
            });
    }

    /**
     * @param IO $io
     * @param array $data
     */
    public function disconnection(IO $io, array $data)
    {
        $io->getCurrentChannel()->remove($data['fd']);
    }

    /**
     * @param IO $io
     * @return Channel
     */
    protected function findOrCreateChannel(IO $io, Request $request): Channel
    {

        $channelName = $request->server['request_uri'] ?? '/';

        if ($io->channelExists($channelName)) {
            return $io->getChannel($channelName);
        }

        $channel = new Channel($channelName, $io, $io->getApplication()->make('websocket.room'));
        $io->join($channel);

        return $channel;
    }
}