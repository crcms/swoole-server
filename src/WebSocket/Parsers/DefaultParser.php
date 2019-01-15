<?php

namespace CrCms\Server\WebSocket\Parsers;

use CrCms\Server\WebSocket\Contracts\ParserContract;
use Swoole\WebSocket\Frame;
use UnexpectedValueException;

/**
 * Class DefaultParse.
 */
class DefaultParser implements ParserContract
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function pack(array $data): string
    {
        return json_encode($data);
    }

    /**
     * @param Frame $frame
     *
     * @return array
     */
    public function unpack(Frame $frame): array
    {
        if ($frame->finish !== true) {
            throw new UnexpectedValueException('The data not full');
        }

        $data = json_decode($frame->data, true);
        if (json_last_error() !== 0) {
            throw new UnexpectedValueException('The data parse error');
        }

        return $data;
    }
}
