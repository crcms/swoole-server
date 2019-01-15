<?php

namespace CrCms\Server\Server\Contracts;

/**
 * Interface TaskServerContract.
 */
interface TaskContract
{
    /**
     * @param mixed ...$params
     *
     * @return mixed
     */
    public function handle(...$params);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function finish($data);
}
