<?php

namespace CrCms\Server\WebSocket;

use Illuminate\Contracts\Container\Container;
use Swoole\WebSocket\Frame;

/**
 * Class Socket.
 */
class Socket
{
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
    protected $frame;

    /**
     * @var int
     */
    protected $fd;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Socket constructor.
     *
     * @param Container $app
     * @param Channel   $channel
     */
    public function __construct(Container $app, Channel $channel)
    {
        $this->app = $app;
        $this->channel = $channel;
    }

    /**
     * @param array $data
     *
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
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param int $fd
     *
     * @return $this
     */
    public function setFd(int $fd)
    {
        $this->fd = $fd;

        return $this;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param Frame $frame
     *
     * @return Socket
     */
    public function setFrame(Frame $frame): self
    {
        $this->frame = $frame;

        return $this;
    }

    /**
     * @return Frame
     */
    public function getFrame(): Frame
    {
        return $this->frame;
    }

    /**
     * @param $room
     *
     * @return Socket
     */
    public function join($room): self
    {
        $this->channel->join($this->getFd(), $room);

        return $this;
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
     * @param mixed  $data
     */
    public function emit(string $event, $data = []): void
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
     * @return array
     */
    public function rooms(): array
    {
        return $this->channel->rooms($this->getFd());
    }
}
