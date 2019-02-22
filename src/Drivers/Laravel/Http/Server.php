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

use CrCms\Server\Drivers\Laravel\Http\Events\Server\RequestEvent;
use CrCms\Server\Drivers\Laravel\Http\Events\Server\WorkerStartEvent;
use CrCms\Server\Drivers\Laravel\Laravel;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\ServerFactory;
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
    protected $container;

    /**
     * @var Laravel
     */
    protected $laravel;

    /**
     * @param array $config
     * @param Laravel $laravel
     */
    public function __construct(array $config, Laravel $laravel)
    {
        $this->events['worker_start'] = WorkerStartEvent::class;
        $this->events['request'] = RequestEvent::class;
        parent::__construct($config);
        $this->laravel = $laravel;
    }

    /**
     * setLaravel
     *
     * @param Laravel $laravel
     * @return $this
     */
    public function setLaravel(Laravel $laravel)
    {
        $this->laravel = $laravel;

        return $this;
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

    /**
     * create
     *
     * @return SwooleServer
     */
    public function create(): SwooleServer
    {
        return ServerFactory::factory('http', $this->baseConfig);
    }

    /**
     * getLaravel
     *
     * @return Laravel
     */
    public function getLaravel(): Laravel
    {
        return $this->laravel;
    }

    /**
     * getApplication
     *
     * @return Container
     */
    public function getApplication(): Container
    {
        return $this->laravel->getApplication();
    }
}
