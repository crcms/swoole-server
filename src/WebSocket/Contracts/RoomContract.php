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
     * @param Socket $socket
     * @param $rooms
     */
    public function add(Socket $socket, $room): void;

    /**
     * @param $room
     */
    public function remove($room): void;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param $room
     * @return array
     */
    public function get($room): array;
}