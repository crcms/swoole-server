<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\Server\Facades\Dispatcher;
use CrCms\Server\WebSocket\Concerns\EventConcern;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use CrCms\Server\WebSocket\Tasks\PushTask;

/**
 * Class Channel
 * @package CrCms\Server\WebSocket
 */
class Channel
{
    use EventConcern;

    /**
     * @var string
     */
    protected static $eventPrefix = 'channel';

    /**
     * @var IO
     */
    protected $io;

    /**
     * @var RoomContract
     */
    protected $room;

    /**
     * @var array
     */
    protected $to = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * Channel constructor.
     * @param IO $io
     * @param string $name
     */
    public function __construct(IO $io, string $name)
    {
        $this->io = $io;
        $this->name = $name;
        $this->app = $io->getApplication();
        $this->room = $io->getRoom();
    }

    /**
     * @param Socket $socket
     * @param $room
     * @return Channel
     */
    public function join(int $fd, $room): self
    {
        $this->room->add($fd, $this->filterGetRooms($room));

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return IO
     */
    public function getIo(): IO
    {
        return $this->io;
    }

    /**
     * @param string|array $room
     */
    public function to($room): self
    {
        $this->to = array_unique(array_merge($this->to, $this->room->get($this->filterGetRooms($room)), $this->filterGetFds($room)));

        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param $event
     * @param mixed $data
     */
    public function emit($event, $data = [])
    {
        /*if (empty($this->to)) {
            $this->to = $this->room->all();
        }*/

        foreach ($this->to as $to) {
            $this->push(intval($to), $event, $data);
        }

        $this->reset();
    }

    /**
     * @param int $fd
     */
    public function remove(int $fd, $room = []): void
    {
        $this->room->remove($fd, $this->filterGetRooms($room));
    }

    /**
     * @param int $fd
     * @return array
     */
    public function rooms(int $fd): array
    {
        return $this->room->keys($fd);
    }

    /**
     * @param int $fd
     * @param string $event
     * @param mixed $data
     */
    protected function push(int $fd, string $event, $data = []): void
    {
        Dispatcher::dispatch(new PushTask, [$fd, $event, $data]);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->to = [];
    }

    /**
     * @param $room
     * @return array
     */
    protected function filterGetFds($room): array
    {
        return array_filter((array)$room, function ($value) {
            return is_integer($value);
        });
    }

    /**
     * @param $room
     * @return array
     */
    protected function filterGetRooms($room): array
    {
        $prefix = 'channel.' . $this->name . '_';

        return array_map(function ($room) use ($prefix) {
            return $prefix . $room;
        }, array_filter((array)$room, function ($value) {
            return !is_integer($value);
        }));
    }
}