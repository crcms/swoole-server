<?php

namespace CrCms\Server\Http\Events;

use CrCms\Server\Http\Request;
use CrCms\Server\Http\Response;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\AbstractEvent;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Illuminate\Contracts\Http\Kernel;

/**
 * Class RequestEvent
 * @package CrCms\Server\Http\Events
 */
class RequestEvent extends AbstractEvent implements EventContract
{
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
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function __construct(SwooleRequest $request, SwooleResponse $response)
    {
        $this->swooleRequest = $request;
        $this->swooleResponse = $response;
    }

    /**
     * @return void
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        $kernel = $server->getApplication()->make(Kernel::class);
        
        $illuminateRequest = Request::make($this->swooleRequest)->getIlluminateRequest();
        $illuminateResponse = $kernel->handle($illuminateRequest);

        Response::make($this->swooleResponse, $illuminateResponse)->toResponse();

        $kernel->terminate($illuminateRequest, $illuminateResponse);
    }
}