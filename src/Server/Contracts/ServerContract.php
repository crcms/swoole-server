<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/17 14:25
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Server\Contracts;

use Swoole\Server;

/**
 * Interface ServerContract.
 */
interface ServerContract
{
    /**
     * Create a server
     *
     * @return void
     */
    //public function create(array $config): Server;

    public function server(): Server;

    public function start(): void;

    public function stop(): void;
}
