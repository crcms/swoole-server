<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Rooms;

use CrCms\Server\WebSocket\Contracts\RoomContract;
use Illuminate\Contracts\Redis\Connection;

/**
 * Class RedisRoom.
 */
class RedisRoom implements RoomContract
{
    /**
     * @var Connection
     */
    protected $redis;

    /**
     * RedisRoom constructor.
     *
     * @param Connection $redis
     */
    public function __construct(Connection $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param int $fd
     * @param $room
     */
    public function add(int $fd, $room): void
    {
        $room = (array) $room;

        $this->redis->pipeline(function ($pipe) use ($fd, $room) {
            foreach ($room as $value) {
                $pipe->sadd($value, $fd);
            }
        });
    }

    /**
     * @param int   $fd
     * @param array $room
     */
    public function remove(int $fd, $room = []): void
    {
        $this->redis->pipeline(function ($pipe) use ($fd, $room) {
            $rooms = empty($room) ?
                $this->redis->keys('*') : (array) $room;

            //merge rooms
            $allRooms = [];
            foreach ($rooms as $ifRoom) {
                if (strpos($ifRoom, '*') !== false) {
                    $allRooms = array_merge($allRooms, $this->redis->keys($ifRoom));
                } else {
                    $allRooms[] = $ifRoom;
                }
            }

            foreach ($allRooms as $valueRoom) {
                $pipe->srem($valueRoom, $fd);
                if ($pipe->scard($valueRoom) === 0) {
                    $pipe->del($valueRoom);
                }
            }
        });
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->get($this->redis->keys('*'));
    }

    /**
     * @param array|string $room
     *
     * @return array
     */
    public function get($room): array
    {
        return array_reduce((array) $room, function ($result, $value) {
            return array_merge($result, $this->redis->smembers($value));
        }, []);
    }

    /**
     * @param int $fd
     *
     * @return array
     */
    public function keys(int $fd): array
    {
        $existsKeys = [];

        $keys = $this->redis->keys('*');
        foreach ($keys as $key) {
            if ($this->redis->sismember($key, $fd)) {
                $existsKeys[] = $key;
            }
        }

        return $existsKeys;
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->redis->flushdb();
    }

    /**
     * @return Connection
     */
    public function connection(): Connection
    {
        return $this->redis;
    }
}