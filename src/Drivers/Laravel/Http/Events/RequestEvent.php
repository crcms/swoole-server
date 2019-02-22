<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Drivers\Laravel\Http\Request;
use CrCms\Server\Drivers\Laravel\Http\Response;
use CrCms\Server\Drivers\Laravel\Http\Server;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use Illuminate\Contracts\Http\Kernel;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * Class RequestEvent.
 */
class RequestEvent extends AbstractEvent
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var SwooleRequest
     */
    protected $swooleRequest;

    /**
     * @var SwooleResponse
     */
    protected $swooleResponse;

    /**
     * Request constructor.
     *
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function __construct(AbstractServer $server, SwooleRequest $request, SwooleResponse $response)
    {
        parent::__construct($server);
        $this->swooleRequest = $request;
        $this->swooleResponse = $response;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        try {
            $this->server->getLaravel()->open();

            $kernel = $this->server->getApplication()->make(Kernel::class);

            $illuminateRequest = Request::make($this->swooleRequest)->getIlluminateRequest();
            $illuminateResponse = $kernel->handle($illuminateRequest);

            Response::make($this->swooleResponse, $illuminateResponse)->toResponse();

            $kernel->terminate($illuminateRequest, $illuminateResponse);

            $this->server->getLaravel()->getBaseContainer()->make('events')->dispatch('request', [$this->server, $this->server->getApplication(), $illuminateRequest, $illuminateResponse]);

        } catch (\Throwable $e) {
            throw $e;
        } finally {
            // reset application
            $this->server->getLaravel()->close();
        }
    }
}
