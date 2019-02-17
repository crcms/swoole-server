<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/16 17:41
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Drivers\Laravel\Http;

use CrCms\Server\Drivers\Laravel\Http\Events\RequestEvent;
use CrCms\Server\Server\AbstractServer;

/**
 * Class Server.
 */
class Server extends AbstractServer
{
    /**
     * @var array
     */
    protected $events = [
        'request' => RequestEvent::class,
    ];

    /**
     * name
     *
     * @return string
     */
    public function name(): string
    {
        return 'laravel_http';
    }
}
