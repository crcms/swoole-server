<?php

namespace CrCms\Server\WebSocket\Parsers;

use CrCms\Server\WebSocket\Contracts\ParserContract;
use Swoole\WebSocket\Frame;
use UnexpectedValueException;

/**
 * Class DefaultParse
 * @package CrCms\Server\WebSocket\Parsers
 */
class DefaultParser implements ParserContract
{
    public function pack()
    {
    }

    public function unpack(Frame $frame): array
    {
        if ($frame->finish !== true) {
            throw new UnexpectedValueException("The data not full");
        }

        $data = json_decode($frame->data, true);
        if (json_last_error() !== 0) {
            throw new UnexpectedValueException("The data parse error");
        }

        return $data;
    }
}