<?php

namespace CrCms\Server\WebSocket\Converters;

use CrCms\Server\WebSocket\Contracts\ConverterContract;

/**
 * Class DefaultConverter
 * @package CrCms\Server\WebSocket\Converters
 */
class DefaultConverter implements ConverterContract
{
    /**
     * @param array $data
     * @return array
     */
    public function conversion(array $data): array
    {
        return array_combine(['event', 'data'], $data);
    }
}