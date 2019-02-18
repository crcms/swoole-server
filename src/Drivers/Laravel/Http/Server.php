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
     * @var Laravel
     */
    protected $laravel;

    /**
     * @param array $config
     * @param ApplicationContract $contract
     */
    public function __construct(array $config,Laravel $laravel)
    {
        $this->events['request'] = RequestEvent::class;
        parent::__construct($config);
        $this->laravel = $laravel;
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
