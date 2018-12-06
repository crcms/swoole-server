<?php

namespace CrCms\Server\WebSocket\Contracts;

use Swoole\WebSocket\Frame;

/**
 * Interface ParserContract
 * @package CrCms\Server\WebSocket\Contracts
 */
interface ParserContract
{
    public function pack();

    public function unpack(Frame $frame);
}