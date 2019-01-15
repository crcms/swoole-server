<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\Events\Internal\CloseHandledEvent;
use CrCms\Server\WebSocket\FdChannelTrait;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;

/**
 * Class CloseEvent.
 */
class CloseEvent extends AbstractEvent
{
    use FdChannelTrait;

    /**
     * @var int
     */
    protected $fd;

    /**
     * CloseEvent constructor.
     *
     * @param $fd
     */
    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    /**
     * @param AbstractServer $server
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        /* @var Container $app */
        $app = $server->getApplication();

        //close websocket
        try {
            $info = $server->getServer()->getClientInfo($this->fd);
            if (is_array($info) && isset($info['websocket_status'])) {
                $this->closeWebSocket($app);
            }
        } finally {
            $app->make('events')->dispatch(
                new CloseHandledEvent($this->fd)
            );
        }
    }

    /**
     * @param Container $app
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function closeWebSocket(Container $app): void
    {
        /* @var Channel $channel */
        try {
            $channel = $this->currentChannel($this->fd);
        } catch (\Exception $exception) {
            echo "closeWebSocket: {$exception->getMessage()}, fd:{$this->fd}".PHP_EOL;
            $channel = null;
        }

        if (is_null($channel)) {
            return;
        }

        $websocket = (new Socket($app, $channel))->setFd($this->fd);

        $app->instance('websocket', $websocket);

        try {
            if ($channel->eventExists('disconnection')) {
                $channel->dispatch('disconnection');
            }
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            $websocket->leave();
        }
    }
}
