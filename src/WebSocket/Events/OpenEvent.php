<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Http\Request;
use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Swoole\Http\Request as SwooleRequest;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use CrCms\Server\WebSocket\Exceptions\Handler as ExceptionHandler;

/**
 * Class OpenEvent
 * @package CrCms\Server\WebSocket\Events
 */
class OpenEvent extends AbstractEvent
{
    /**
     * @var SwooleRequest
     */
    protected $request;

    /**
     * @var IlluminateRequest
     */
    protected $illuminateRequest;

    /**
     * OpenEvent constructor.
     * @param SwooleRequest $request
     */
    public function __construct(SwooleRequest $request)
    {
        $this->request = $request;
        $this->illuminateRequest = (new Request($this->request))->getIlluminateRequest();
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        /* @var Container $app */
        $app = $this->server->getApplication();
        /* @var string $channelName */
        $channelName = $this->channelName();
        /* @var Channel $channel */
        $channel = IO::getChannel($channelName)->join($this->request->fd, '_global_channel_' . $channelName);

        // bind websocket instance
        $app->instance('websocket', (new Socket($app, $channel))->setFd($this->request->fd));

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
                    'request' => $this->illuminateRequest
                ]);
            }
        } catch (\Exception $e) {
            $app->make(ExceptionHandler::class)->render($app->make('websocket'), $e);
            throw $e;
        } catch (\Throwable $e) {
            $e = new FatalThrowableError($e);
            $app->make(ExceptionHandler::class)->render($app->make('websocket'), $e);
            throw $e;
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