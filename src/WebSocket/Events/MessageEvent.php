<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Events\Internal\MessageHandledEvent;
use CrCms\Server\WebSocket\Events\Internal\MessageHandleEvent;
use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\WebSocket\Socket;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use OutOfBoundsException;

/**
 * Class MessageEvent
 * @package CrCms\Framework\Http\Events
 */
class MessageEvent extends AbstractEvent implements EventContract
{
    /**
     * @var object
     */
    protected $frame;

    /**
     * MessageEvent constructor.
     * @param $frame
     */
    public function __construct($frame)
    {
        $this->frame = $frame;
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        /* @var Container $app */
        $app = $server->getApplication();

        try {
            /* @var Channel $channel */
            $channel = IO::of($this->channelName());
            /* 解析数据 @var array $payload */
            $payload = $app->make('websocket.parser')->unpack($this->frame);
        } catch (\Throwable $e) {
            $server->getServer()->disconnect($this->request->fd, 1003, 'unsupported.');
            throw $e;
        }

        // Create socket
        $socket = (new Socket($app, $channel))->setData($payload['data'] ?? [])->setFrame($this->frame)->setFd($this->frame->fd);

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
            $app->make(ExceptionHandler::class)->render($socket, $e);
            throw $e;
        } catch (\Throwable $e) {
            $throwable = new FatalThrowableError($e);
            $app->make(ExceptionHandler::class)->render($socket, $throwable);
            throw $e;
        } finally {
            $app->make('events')->dispatch(
                new MessageHandledEvent($socket)
            );
        }
    }

    /**
     * @return string
     */
    protected function channelName(): string
    {
        $channels = IO::getChannels();

        $currentRoom = '';

        foreach ($channels as $channel) {
            $rooms = $channel->rooms($this->frame->fd);
            if ($rooms) {
                foreach ($rooms as $room) {
                    if (stripos($room, '_global_channel_')) {
                        $currentRoom = $room;
                        break;
                    }
                }
            }

            if ($currentRoom) {
                break;
            }
        }

        if (empty($currentRoom)) {
            throw new \RangeException("The channel not found");
        }

        return strrchr($currentRoom, '/');
    }
}