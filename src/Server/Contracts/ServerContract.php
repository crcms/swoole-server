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
     * A server name
     *
     * @return string
     */
    public function name(): string;

    /**
     * Create a server
     *
     * @return void
     */
    public function create(): void;

    /**
     * Return a swoole server
     *
     * @return Server
     */
    public function server(): Server;
}
