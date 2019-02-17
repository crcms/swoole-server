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

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\Http\Events\RequestEvent;
use CrCms\Server\Server\AbstractServer;
use Illuminate\Contracts\Container\Container;
use Swoole\Server as SwooleServer;

/**
 * Class Server.
 */
class Server extends AbstractServer
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var array
     */
    protected $events = [
        'request' => RequestEvent::class,
    ];

    public function __construct(array $config, Container $app)
    {
        parent::__construct($config);
        $this->app = $app;
    }

    /**
     * name
     *
     * @return string
     */
    public function name(): string
    {
        return 'laravel_http';
    }

    public function getApplication()
    {
        return $this->app;
    }

    public function create(): SwooleServer
    {
        // TODO: Implement create() method.
    }


}
