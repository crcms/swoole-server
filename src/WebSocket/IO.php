<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Contracts\RoomContract;
use Illuminate\Contracts\Container\Container;
use OutOfRangeException;

/**
 * Class IO
 * @package CrCms\Server\WebSocket
 */
class IO
{
    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var RoomContract
     */
    protected $room;

    /**
     * IO constructor.
     * @param Container $app
     * @param RoomContract $room
     */
    public function __construct(Container $app, RoomContract $room)
    {
        $this->app = $app;
        $this->room = $room;
    }

    /**
     * @return Container
     */
    public function getApplication(): Container
    {
        return $this->app;
    }

    /**
     * @return RoomContract
     */
    public function getRoom(): RoomContract
    {
        return $this->room;
    }

    /**
     * @param Channel $channel
     * @return IO
     */
    public function addChannel(Channel $channel): self
    {
        $channelName = $channel->getName();

        if (!isset($this->channels[$channelName])) {
            $this->channels[$channelName] = $channel;
        }

        return $this;
    }

    /**
     * @param Channel $channel
     * @return $this
     */
    public function setChannel(Channel $channel)
    {
        $this->channels[$channel->getName()] = $channel;

        return $this;
    }

    /**
     * @param string $channel
     * @return Channel
     */
    public function of(string $channel): Channel
    {
        return $this->getChannel($channel);
    }

    /**
     * @param string $channel
     * @return Channel
     */
    public function getChannel(string $channel): Channel
    {
        if (!$this->channelExists($channel)) {
            throw new OutOfRangeException("The channel[{$channel}] not found");
        }

        return $this->channels[$channel];
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
     * @return bool
     */
    public function channelExists(string $channel): bool
    {
        return isset($this->channels[$channel]);
    }
}