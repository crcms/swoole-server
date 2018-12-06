<?php

namespace CrCms\Server\WebSocket\Rooms;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Support\Arr;

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
     * @param Socket $socket
     * @param $room
     */
    public function add(Socket $socket, $room): void
    {
        foreach ((array)$room as $value) {
            $this->rooms[$value][$socket->getFd()] = $socket;
        }
    }

    /**
     * @param $room
     */
    public function remove($room): void
    {
        extract($this->getFdsAndRoom((array)$room));

        //先删除所有room
        $this->rooms = Arr::only($this->rooms, $room);

        //移除fds
        if ($fds) {
            foreach ($this->rooms as $room) {
                $room = Arr::only($room, array_flip($fds));
            }
        }
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->rooms;
    }

    /**
     * @param $room
     * @return array
     */
    public function get($room): array
    {
        extract($this->getFdsAndRoom((array)$room));

        if ($fds) {
            //有fds合并所有fds [fd1,fd2,fd3]
            $otherRooms = Arr::except($this->rooms, $room);
            //取出所有fd的值
            $fds = array_reduce($otherRooms, function ($result, $value) use ($fds) {
                return array_merge($result, Arr::only($value, $fds));
            }, []);

            return array_merge(Arr::only($this->rooms, $room), $fds);
        }

        // 无fds返回所有room, [room=>[fd1=>$socket,fd2=>$socket]]
        return Arr::only($this->rooms, $room);
    }

    /**
     * @param $room
     * @return array
     */
    protected function getFdsAndRoom($room): array
    {
        $fds = array_filter($room, function ($room) {
            return is_integer($room);
        });

        $room = array_diff($room, $fds);

        return compact('fds', 'room');
    }
}