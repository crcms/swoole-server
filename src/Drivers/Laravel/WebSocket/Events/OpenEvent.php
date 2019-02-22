<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Http\Request;
use CrCms\Server\Drivers\Laravel\WebSocket\Server;
use function CrCms\Server\Drivers\Laravel\websocket_exception_report_render;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\Drivers\Laravel\WebSocket\Channel;
use CrCms\Server\WebSocket\ConnectionHandled;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;
use CrCms\Server\Drivers\Laravel\Facades\IO;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Pipeline\Pipeline;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class OpenEvent.
 */
class OpenEvent extends AbstractEvent
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var SwooleRequest
     */
    protected $request;

    /**
     * @var IlluminateRequest
     */
    protected $illuminateRequest;

    /**
     * @param AbstractServer $server
     * @param SwooleRequest $request
     */
    public function __construct(AbstractServer $server, SwooleRequest $request)
    {
        parent::__construct($server);
        $this->request = $request;
        $this->illuminateRequest = (new Request($this->request))->getIlluminateRequest();
    }

    /**
     * handle
     *
     * @return void
     *
     * @throws FatalThrowableError
     * @throws \Throwable
     */
    public function handle(): void
    {
        /* @var Container $app */
        $app = $this->server->getApplication();

        $this->server->getLaravel()->open();

        try {
            /* @var Channel $channel */
            $channel = IO::getChannel($this->channelName());

            //join room
            $channel->join($this->request->fd, strval($this->request->fd));

            // bind websocket instance
            $app->instance('websocket', new Socket($channel, $this->request->fd));
        } catch (\Throwable $e) {
            $this->server->getServer()->disconnect($this->request->fd, 1003, 'unsupported.');

            websocket_exception_report_render($app, $e);

            $this->server->getLaravel()->close();

            throw $e;
        }

        try {
            // middleware
            (new Pipeline($app))
                ->send($this->illuminateRequest)
                ->through(config('swoole.websocket_request_middleware'))
                ->then(function (IlluminateRequest $request) {
                    return $request;
                });

            // dispatch
            if ($channel->eventExists('connection')) {
                $channel->dispatch('connection', [
                    'app' => $app,
                    'request' => $this->illuminateRequest,
                ]);
            }
        } catch (\Throwable $e) {
            websocket_exception_report_render($app, $e, $app->make('websocket'));

            throw $e;
        } finally {
            $this->server->getLaravel()->close();
        }
    }

    /**
     * @return string
     */
    protected function channelName(): string
    {
        return $this->request->server['request_uri'] ?? '/';
    }
}
