<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket;

use CrCms\Server\Drivers\Laravel\Facades\Dispatcher;
use CrCms\Server\Drivers\Laravel\WebSocket\Tasks\PushTask;
use CrCms\Server\WebSocket\AbstractChannel;
use CrCms\Server\WebSocket\IO;
use Illuminate\Contracts\Container\Container;

class Channel extends AbstractChannel
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @param Container $app
     * @param IO $io
     * @param string $name
     */
    public function __construct(Container $app, IO $io, string $name)
    {
        $this->app = $app;
        parent::__construct($io, $name);
    }

    /**
     * push
     *
     * @param int $fd
     * @param string $event
     * @param array $data
     * @return void
     */
    protected function push(PushTask $task, int $fd, string $event, array $data = []): void
    {//[$fd, $event, $data]
        Dispatcher::dispatch($task, [$fd, $event, $data]);
    }

    /**
     * call
     *
     * @param $call
     * @param array $data
     * @return void
     */
    protected function call($call, array $data = []): void
    {
        $this->app->call($call, $data);
    }
}