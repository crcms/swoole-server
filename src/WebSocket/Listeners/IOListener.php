<?php

namespace CrCms\Server\WebSocket\Listeners;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\IO;
use CrCms\Server\WebSocket\Socket;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;
use Illuminate\Pipeline\Pipeline;
use Swoole\Http\Request;
use Symfony\Component\Debug\Exception\FatalThrowableError;

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

        $app = $io->getApplication();

        //bind instance
        $app->instance('socket', $socket);

        //中间件调度
        (new Pipeline($data['app']))
            ->send($socket)
            ->through(config('swoole.websocket_middleware'))
            ->then(function (Socket $socket) use ($app) {
                //event dispatch 触发事件
                $frame = $socket->getFrame();
                try {
                    $socket->dispatch($frame['event'], $frame['data']);
                } catch (\Exception $e) {
                    $app->make(ExceptionHandler::class)->report($e);
                    $app->make(ExceptionHandler::class)->render($socket, $e);
                    throw $e;
                } catch (\Throwable $e) {
                    $e = new FatalThrowableError($e);
                    $app->make(ExceptionHandler::class)->report($e);
                    $app->make(ExceptionHandler::class)->render($socket, $e);
                    throw $e;
                }
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