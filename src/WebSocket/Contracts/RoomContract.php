<?php

namespace CrCms\Server\WebSocket\Contracts;

/**
 * Interface RoomContract.
 */
interface RoomContract
{
    /**
     * @param int $fd
     * @param $room
     */
    public function add(int $fd, $room): void;

    /**
     * @param string|array $room
     *
     * @return array
     */
    public function get($room): array;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param int $fd
     *
     * @return array
     */
    public function keys(int $fd): array;

    /**
     * @param int          $fd
     * @param array|string $room
     */
    public function remove(int $fd, $room = []): void;

    /**
     * @return void
     */
    public function reset(): void;

    /**
     * @return mixed
     */
    public function connection();
}
