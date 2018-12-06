<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use Illuminate\Contracts\Foundation\Application;
use Swoole\Http\Request;
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
     * @var Application
     */
    protected $app;

    /**
     * IO constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Channel $channel
     * @param string $name
     * @return IO
     */
    public function join(Channel $channel, string $name): self
    {
        $this->channels[$name] = $channel;

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