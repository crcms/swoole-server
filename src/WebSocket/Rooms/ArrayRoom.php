<?php

namespace CrCms\Server\WebSocket\Rooms;

use CrCms\Server\WebSocket\Contracts\RoomContract;

/**
 * Class ArrayRoom
 * @package CrCms\Server\WebSocket\Rooms
 */
class ArrayRoom implements RoomContract
{
    /**
     * @var array
     */
    protected $rooms;

    /**
     * ArrayRoom constructor.
     */
    public function __construct()
    {
        $this->rooms = [];
    }

    /**
     * @param int $fd
     * @param $room
     */
    public function add(int $fd, $room): void
    {
        foreach ((array)$room as $value) {
            $this->rooms[$value][] = $fd;
        }
    }

    /**
     * @param array|string $room
     * @return array
     */
    public function get($room): array
    {
        return array_reduce((array)$room, function ($result, $value) {
            return array_merge($result, $this->rooms[$value] ?? []);
        }, []);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->get(array_keys($this->rooms));
    }

    /**
     * @param int $fd
     * @param array $room
     */
    public function remove(int $fd, $room = []): void
    {
        $rooms = $room ? (array)$room : array_keys($this->rooms);

        foreach ($rooms as $roomKey) {
            $this->rooms[$roomKey] = array_diff($this->rooms[$roomKey], [$fd]);
        }
    }
}