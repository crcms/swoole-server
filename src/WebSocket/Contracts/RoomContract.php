<?php

namespace CrCms\Server\WebSocket\Contracts;

use CrCms\Server\WebSocket\Socket;

/**
 * Interface RoomContract
 * @package CrCms\Server\WebSocket\Contracts
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
     * @return array
     */
    public function get($room): array;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param int $fd
     * @return array
     */
    public function remove(int $fd): void;
}