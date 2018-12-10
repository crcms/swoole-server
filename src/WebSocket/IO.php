<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use Illuminate\Contracts\Container\Container;
use OutOfRangeException;

/**
 * Class IO
 * @package CrCms\Server\WebSocket
 */
class IO
{
    use EventConcern;

    /**
     * @var string
     */
    protected static $eventPrefix = 'io';

    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @var Channel
     */
    protected $currentChannel;

    /**
     * @var Container
     */
    protected $app;

    /**
     * IO constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @return Container
     */
    public function getApplication(): Container
    {
        return $this->app;
    }

    /**
     * @param Channel $channel
     * @return IO
     */
    public function join(Channel $channel): self
    {
        $this->channels[$channel->getName()] = $channel;

        return $this;
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
     * @param Channel $channel
     * @return $this
     */
    public function setCurrentChannel(Channel $channel)
    {
        $this->currentChannel = $channel;
        return $this;
    }

    /**
     * @param string $channel
     * @return bool
     */
    public function channelExists(string $channel): bool
    {
        return isset($this->channels[$channel]);
    }

    /**
     * @return Channel
     */
    public function getCurrentChannel(): Channel
    {
        return $this->currentChannel;
    }
}