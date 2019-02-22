<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Facades\IO;
use CrCms\Server\Drivers\Laravel\WebSocket\Events\Internal\CloseHandledEvent;
use CrCms\Server\Drivers\Laravel\WebSocket\Server;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Events\AbstractEvent;
use CrCms\Server\Drivers\Laravel\WebSocket\Channel;
use CrCms\Server\WebSocket\Socket;
use Illuminate\Contracts\Container\Container;

/**
 * Class CloseEvent.
 */
class CloseEvent extends AbstractEvent
{

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var int
     */
    protected $fd;

    /**
     * CloseEvent constructor.
     *
     * @param $fd
     */
    public function __construct(AbstractServer $server,int $fd)
    {
        parent::__construct($server);
        $this->fd = $fd;
    }

    /**
     * @param AbstractServer $server
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle(): void
    {
        /* @var Container $app */
        $app = $this->server->getApplication();

        $this->server->getLaravel()->open();

        //close websocket
        try {
            $info = $this->server->getServer()->getClientInfo($this->fd);
            //当在websocket cliet中使用http访问时，也会带上websocket_status参数，状态为0
            if (is_array($info) && isset($info['websocket_status']) && $info['websocket_status'] > 0) {
                $this->closeWebSocket($app);
            }
        } finally {
            $this->server->getLaravel()->close();
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
//        try {
//            $channel = IO::getFdChannel($this->fd);
//        } catch (\Exception $exception) {
//            echo "closeWebSocket: {$exception->getMessage()}, fd:{$this->fd}".PHP_EOL;
//            $channel = null;
//        }

        $channel = IO::getFdChannel($this->fd);
        if (is_null($channel)) {
            return;
        }

        $websocket = (new Socket($channel, $this->fd));

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
