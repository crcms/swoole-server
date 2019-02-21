<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Http\Events\WorkerStartEvent as BaseWorkerStartEvent;

class WorkerStartEvent extends BaseWorkerStartEvent
{

    public function handle(): void
    {
        $this->server->getApplication()->singleton('websocket.io', function ($app) {
            $io = new IO($app['websocket.room']);
            $channels = $app['config']->get('swoole.websocket_channels', ['/']);
            foreach ($channels as $channel) {
                $io->addChannel(new Channel($io, $channel));
            }

            return $io;
        });

        parent::handle();
    }
}