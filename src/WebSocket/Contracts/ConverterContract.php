<?php

namespace CrCms\Server\WebSocket\Contracts;

/**
 * Interface ConverterContract
 * @package CrCms\Server\WebSocket\Contracts
 */
interface ConverterContract
{
    /**
     * @param array $data
     * @return array
     */
    public function conversion(array $data): array;
}