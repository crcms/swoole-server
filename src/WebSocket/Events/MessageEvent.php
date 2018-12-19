<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\WebSocket\Socket;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;
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

        $app = $server->getApplication();

        //解析数据
        $frame = $app->make('websocket.parser')->unpack($this->frame);

        $socket = (new Socket($app, IO::getChannel($this->channelName())))->setData($frame['data'] ?? [])->setFrame($this->frame)->setFd($this->frame->fd);

        $app->instance('websocket', $socket);

        try {
            if ($socket::eventExists($frame['event'])) {
                $socket->dispatch($frame['event'], $frame['data']);
            } else {
                throw new OutOfBoundsException("The event[{$frame['event']}] not found");
            }
        } catch (\Exception $e) {
            $app->make(ExceptionHandler::class)->report($e);
            $app->make(ExceptionHandler::class)->render($socket, $e);
        } catch (\Throwable $e) {
            $e = new FatalThrowableError($e);
            $app->make(ExceptionHandler::class)->report($e);
            $app->make(ExceptionHandler::class)->render($socket, $e);
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