<?php

namespace CrCms\Server\WebSocket\Listeners;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\IO;
use CrCms\Server\WebSocket\Socket;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

/**
 * Class IOListener
 * @package CrCms\Server\WebSocket\Listeners
 */
class IOListener
{
    /**
     * @param IO $io
     * @param array $data
     */
    public function connection(IO $io, array $data)
    {
        dump(111111111111111111);
        $currentChannel = $this->findOrCreateChannel($io, $data['request']);

        $currentChannel->join(
            new Socket($data['app'], $currentChannel, $data['fd'])
        );
        echo $data['fd'];
        echo '======================';
        $io->setCurrentChannel($currentChannel);
    }

    public function message(IO $io,array $data)
    {        dump(2222222222222222);
        $frame = $data['frame'];
        /* @var Socket $socket */
        $socket = $io->getCurrentChannel()->get($frame->fd)[0];
        echo "fd:".strval($socket->getFd());

//        echo $socket->getFd();
//        echo '====================';

        $socket->setFrame($frame);

        //解析数据触发事件
        $data = app('websocket.parser')->unpack($frame);
var_dump($data);
        // 调用 event dispatch 来触发事件
        $socket->dispatch($data['event'], $data['data']);
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
        print_r($request->server);
        $channelName = $request->server['request_uri'] ?? '/';

        if ($io->channelExists($channelName)) {
            return $io->getChannel($channelName);
        }

        $channel = new Channel($io, app('websocket.room'));
        $io->join($channel,$channelName);

        return $channel;
    }
}