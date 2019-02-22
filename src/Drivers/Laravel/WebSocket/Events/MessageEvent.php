<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Facades\IO;
use CrCms\Server\Drivers\Laravel\WebSocket\Server;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\Drivers\Laravel\WebSocket\Channel;
use CrCms\Server\WebSocket\Events\Internal\MessageHandledEvent;
use CrCms\Server\WebSocket\Events\Internal\MessageHandleEvent;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;
use CrCms\Server\WebSocket\FdChannelTrait;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;
use OutOfBoundsException;
use Swoole\WebSocket\Frame;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class MessageEvent.
 */
class MessageEvent extends AbstractEvent
{
    use FdChannelTrait;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var object
     */
    protected $frame;

    /**
     * MessageEvent constructor.
     *
     * @param $frame
     */
    public function __construct(AbstractServer $server, Frame $frame)
    {
        parent::__construct($server);
        $this->frame = $frame;
    }

    public function handle(): void
    {
        /* @var Container $app */
        $app = $this->server->getApplication();

        $this->server->getLaravel()->open();

        try {
            /* @var Channel $channel */
            $channel = IO::getFdChannelOrFail($this->frame->fd);
            /* 解析数据 @var array $payload */
            $payload = $app->make('websocket.parser')->unpack($this->frame);
        } catch (\Throwable $e) {
            $this->server->getServer()->disconnect($this->frame->fd, 1003, 'unsupported.');

            throw $e;
        }

        // Create socket
        $socket = (new Socket($channel, $this->frame->fd))->setData($payload['data'] ?? [])->setFrame($this->frame);

        //bind instance
        $app->instance('websocket', $socket);

        try {
            $app->make('events')->dispatch(
                new MessageHandleEvent($socket)
            );

            if ($channel->eventExists('message')) {
                $channel->dispatch('message');
            }

            if ($channel->eventExists($payload['event'])) {
                $channel->dispatch($payload['event']);
            } else {
                throw new OutOfBoundsException("The event[{$payload['event']}] not found");
            }
        } catch (\Exception $e) {
            $app->make(ExceptionHandler::class)->report($e);
            $app->make(ExceptionHandler::class)->render($socket, $e);

            throw $e;
        } catch (\Throwable $e) {
            $throwable = new FatalThrowableError($e);
            $app->make(ExceptionHandler::class)->report($e);
            $app->make(ExceptionHandler::class)->render($socket, $throwable);

            throw $e;
        } finally {
            $app->make('events')->dispatch(
                new MessageHandledEvent($socket)
            );
        }
    }
}
