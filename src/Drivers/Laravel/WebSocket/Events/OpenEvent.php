<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Http\Request;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\ConnectionHandled;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;
use CrCms\Server\WebSocket\Facades\IO;
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
     * @var SwooleRequest
     */
    protected $request;

    /**
     * OpenEvent constructor.
     *
     * @param SwooleRequest $request
     */
    public function __construct(AbstractServer $server, SwooleRequest $request)
    {
        parent::__construct($server);
        $this->request = $request;
    }

    /**
     */
    public function handle(): void
    {
        /* @var Container $app */
        $app = $this->server->getApplication();

        try {
            /* @var string $channelName */
            $channelName = $this->channelName();
            /* @var Channel $channel */
            $channel = IO::getChannel($channelName);

            //join room
            $channel->join($this->request->fd, strval($this->request->fd));

            // bind websocket instance
            $app->instance('websocket', (new Socket($app, $channel))->setFd($this->request->fd));
        } catch (\Throwable $e) {
            $server->getServer()->disconnect($this->request->fd, 1003, 'unsupported.');

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
        } catch (\Exception $e) {
            $app->make(ExceptionHandler::class)->render($app->make('websocket'), $e);

            throw $e;
        } catch (\Throwable $e) {
            $e = new FatalThrowableError($e);
            $app->make(ExceptionHandler::class)->render($app->make('websocket'), $e);

            throw $e;
        } finally {
            // dispatch an event
            $app->make('events')->dispatch(
                new ConnectionHandled($this->illuminateRequest)
            );
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
