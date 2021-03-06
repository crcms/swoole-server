<?php

namespace CrCms\Server\WebSocket\Contracts;

use Swoole\WebSocket\Frame;

/**
 * Interface ParserContract.
 */
interface ParserContract
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function pack(array $data): string;

    /**
     * @param Frame $frame
     *
     * @return mixed
     */
    public function unpack(Frame $frame);
}
