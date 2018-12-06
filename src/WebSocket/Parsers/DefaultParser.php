<?php

namespace CrCms\Server\WebSocket\Parsers;

use CrCms\Server\WebSocket\Contracts\ParserContract;
use Swoole\WebSocket\Frame;

/**
 * Class DefaultParse
 * @package CrCms\Server\WebSocket\Parsers
 */
class DefaultParser implements ParserContract
{
    public function pack()
    {
        // TODO: Implement pack() method.
    }

    public function unpack(Frame $frame): array
    {
        return json_decode($frame->data,true);
    }
}