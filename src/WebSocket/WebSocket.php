<?php

namespace CrCms\Server\WebSocket;

use RangeException;
use Swoole\WebSocket\Frame;

class WebSocket
{
    protected $io;

    public function __construct(IO $io)
    {
        $this->io = $io;
    }

    /**
     * connection
     *
     * @param string $channel
     * @param int $fd
     * @return void
     */
    public function joinInitializeRoom(string $channel, int $fd)
    {
        $this->io->getChannel($channel)->join($fd, strval($fd));
    }

    public function payload(Frame $frame)
    {
        /* @var AbstractChannel $channel */
        $channel = $this->fdChannel($frame->fd);
        /* 解析数据 @var array $payload */
        $payload = $app->make('websocket.parser')->unpack($this->frame);
    }

    /**
     * fdChannel
     *
     * @param int $fd
     * @return AbstractChannel|null
     */
    public function fdChannel(int $fd)
    {
        $channels = $this->io->getChannels();

        $currentChannel = null;

        /* @var AbstractChannel $channel */
        foreach ($channels as $channel) {
            $rooms = $channel->rooms($fd);
            foreach ($rooms as $room) {
                if ($room === $channel->channelPrefix().strval($fd)) {
                    return $channel;
                }
            }
        }

        return $currentChannel;
    }

    /**
     * fdChannelOrFail
     *
     * @param int $fd
     * @return AbstractChannel
     */
    public function fdChannelOrFail(int $fd): AbstractChannel
    {
        $channel = $this->fdChannel($fd);

        if (is_null($channel)) {
            throw new RangeException('The channel not found');
        }

        return $channel;
    }
}