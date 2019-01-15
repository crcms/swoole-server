<?php

namespace CrCms\Server\WebSocket\Contracts;

/**
 * Interface ConverterContract.
 */
interface ConverterContract
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function conversion(array $data): array;
}
