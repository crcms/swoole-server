<?php

namespace CrCms\Server\WebSocket\Converters;

use CrCms\Server\WebSocket\Contracts\ConverterContract;

/**
 * Class DefaultConverter.
 */
class DefaultConverter implements ConverterContract
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function conversion(array $data): array
    {
        /* @var $data[0] event */
        /* @var $data[1] data */
        return array_merge(['event'=>$data[0]], (array) $data[1]);
    }
}
