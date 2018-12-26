<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;

/**
 * Class CloseEvent
 * @package CrCms\Server\WebSocket\Events
 */
class CloseEvent extends AbstractEvent
{
    /**
     * @var int
     */
    protected $fd;

    /**
     * CloseEvent constructor.
     * @param $fd
     */
    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    /**
     * @param AbstractServer $server
     * @throws \Exception
     * @return void
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        /* @var Container $app */
        $app = $server->getApplication();

        //close websocket
        $info = $server->getServer()->getClientInfo($this->fd);
        if (is_array($info) && isset($info['websocket_status'])) {
            $this->closeWebSocket($app);
        }
    }

    /**
     * @param Container $app
     * @throws \Exception
     * @return void
     */
    protected function closeWebSocket(Container $app): void
    {
        /* @var Channel $channel */
        try {
            $channel = IO::getChannel($this->channelName());
        } catch (\Exception $exception) {
            $channel = null;
        }

        if (is_null($channel)) {
            return;
        }

        $websocket = (new Socket($app, $channel))->setFd($this->fd);

        $app->instance('websocket', $websocket);

        try {
            if ($channel->eventExists('disconnection')) {
                $channel->dispatch('disconnection');
            }
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            $websocket->leave();
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
            $rooms = $channel->rooms($this->fd);
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