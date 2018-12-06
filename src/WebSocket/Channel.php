<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Concerns\EventConcern;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use CrCms\Server\WebSocket\Rooms\ArrayRoom;

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
     * Channel constructor.
     * @param IO $io
     * @param RoomContract $room
     */
    public function __construct(IO $io, RoomContract $room)
    {
        $this->io = $io;
        $this->room = $room;
    }

    /**
     * @param Socket $socket
     * @param $room _hall 就每个channel里面的默认room
     * @return Channel
     */
    public function join(Socket $socket, $room = '_hall'): self
    {
        $this->room->add($socket, $this->fullRoomName($room));

        return $this;
    }

    /**
     * @return IO
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @param string $room
     */
    public function to($room)//到哪个房间
    {
        $this->to = $this->get($room);
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
     * @param $room
     * @return array
     */
    public function get($room): array
    {
        return $this->room->get($this->fullRoomName($room));
    }

    /**
     * @param $room
     */
    public function remove($room): void
    {
        $this->room->remove($this->fullRoomName($room));
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
     * @return array|string
     */
    protected function fullRoomName($room)
    {
        $prefix = spl_object_hash($this);

        return is_array($room) ?
            array_map(function ($room) use ($prefix) {
                return is_integer($room) ? $room : $prefix . '_' . $room;
            }, $room) :
            is_integer($room) ? $room : $prefix . '_' . $room;
    }
}