<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-02-22 21:34
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Drivers\Laravel\Http\Server;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandledEvent
{
    /**
     * @var Server
     */
    public $server;

    /**
     * @var Container
     */
    public $app;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @param Server $server
     * @param Container $app
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Server $server, Container $app, Request $request, Response $response)
    {
        $this->server = $server;
        $this->app = $app;
        $this->request = $request;
        $this->response = $response;
    }
}