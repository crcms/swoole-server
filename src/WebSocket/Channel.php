<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use CrCms\Server\WebSocket\Contracts\RoomContract;

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
     * Channel constructor.
     * @param string $name
     * @param IO $io
     * @param RoomContract $room
     */
    public function __construct(string $name, IO $io, RoomContract $room)
    {
        $this->name = $name;
        $this->io = $io;
        $this->room = $room;
    }

    /**
     * @param Socket $socket
     * @param $room
     * @return Channel
     */
    public function join(int $fd, $room): self
    {
        $this->room->add($fd, $this->getRooms($room));

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
     * @param string $room
     */
    public function to($room): self
    {
        $this->to = array_merge($this->getRooms($room), $this->getFds($room));
        return $this;
    }

    /**
     * @param $event
     * @param array $data
     */
    public function emit($event, array $data = [])
    {
        foreach ($this->to as $to) {
            /* @var Socket $socket */
            if (is_array($to)) {
                foreach ($to as $socket) {
                    $socket->emit($event, $data);
                }
            } else {
                $to->emit($event, $data);
            }
        }

        $this->reset();
    }

    /**
     * @param int $fd
     */
    public function remove(int $fd): void
    {
        $this->room->remove($fd);
    }

    /**
     * @return void
     */
    protected function reset(): void
    {
        $this->to = [];
    }

    /**
     * @param $room
     * @return array
     */
    protected function getFds($room): array
    {
        return array_filter((array)$room, function ($value) {
            return is_integer($value);
        });
    }

    /**
     * @param $room
     * @return array
     */
    protected function getRooms($room): array
    {
        $prefix = $this->name . '_';

        return array_map(function ($room) use ($prefix) {
            return $prefix . $room;
        }, array_filter((array)$room, function ($value) {
            return !is_integer($value);
        }));
    }
}