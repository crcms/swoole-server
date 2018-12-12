<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use Illuminate\Contracts\Container\Container;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use BadMethodCallException;

/**
 * Class Socket
 * @package CrCms\Server\WebSocket
 */
class Socket
{
    use EventConcern;

    /**
     * @var string
     */
    protected static $eventPrefix = 'socket';

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Channel
     */
    public $channel;

    /**
     * @var Frame
     */
    protected $originalFrame;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $frame;

    /**
     * Socket constructor.
     * @param Container $app
     * @param Request $request
     * @param Channel $channel
     * @param Frame $originalFrame
     * @param array $frame
     */
    public function __construct(Container $app, Request $request, Channel $channel, Frame $originalFrame, array $frame = [])
    {
        $this->app = $app;
        $this->request = $request;
        $this->channel = $channel;
        $this->originalFrame = $originalFrame;
        $this->frame = $frame;
    }

    /**
     * @param array $data
     * @return Socket
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getFrame(): array
    {
        return $this->frame;
    }

    /**
     * @param $room
     * @return Socket
     */
    public function join($room): self
    {
        $this->channel->join($this->originalFrame->fd, $room);
        return $this;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->originalFrame->fd;
    }

    /**
     * @return Channel
     */
    public function broadcast(): Channel
    {
        return $this->channel;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param string $event
     * @param array $data
     */
    public function emit(string $event, array $data = []): void
    {
        $this->channel->to($this->getFd())->emit($event, $data);
    }

    /**
     * @param array $room
     */
    public function leave($room = []): void
    {
        $this->channel->remove($this->getFd(), $room);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->channel, $name)) {
            array_unshift($arguments, $this);
            return $this->channel->$name(...$arguments);
        }

        throw new BadMethodCallException("The method[{$name}] not found");
    }
}