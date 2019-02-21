<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Contracts\RoomContract;
use OutOfRangeException;
use RangeException;

/**
 * Class IO.
 */
class IO
{
    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @var RoomContract
     */
    protected $room;

    /**
     * IO constructor.
     *
     * @param RoomContract $room
     */
    public function __construct(RoomContract $room)
    {
        $this->room = $room;
    }

    /**
     * @return RoomContract
     */
    public function getRoom(): RoomContract
    {
        return $this->room;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return IO
     */
    public function addChannel(AbstractChannel $channel): self
    {
        $channelName = $channel->getName();

        if (!isset($this->channels[$channelName])) {
            $this->channels[$channelName] = $channel;
        }

        return $this;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return $this
     */
    public function setChannel(AbstractChannel $channel)
    {
        $this->channels[$channel->getName()] = $channel;

        return $this;
    }

    /**
     * @param string $channel
     *
     * @return AbstractChannel
     */
    public function of(string $channel): AbstractChannel
    {
        return $this->getChannel($channel);
    }

    /**
     * @param string $channel
     *
     * @return AbstractChannel
     */
    public function getChannel(string $channel): AbstractChannel
    {
        if (!$this->channelExists($channel)) {
            throw new OutOfRangeException("The channel[{$channel}] not found");
        }

        return $this->channels[$channel];
    }

    /**
     * Get channel by fd
     *
     * @param int $fd
     * @return AbstractChannel|null
     */
    public function getFdChannel(int $fd)
    {
        $channels = $this->getChannels();

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
     * Get channel by fd
     * If not found throw
     *
     * @param int $fd
     * @return AbstractChannel
     */
    public function getFdChannelOrFail(int $fd): AbstractChannel
    {
        $channel = $this->getFdChannel($fd);

        if (is_null($channel)) {
            throw new RangeException('The channel not found');
        }

        return $channel;
    }

    /**
     * @return array
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @param string $channel
     *
     * @return bool
     */
    public function channelExists(string $channel): bool
    {
        return isset($this->channels[$channel]);
    }
}
