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
     * @param string $name
     * @param Container $container
     * @param IO $io
     * @param PushTask $task
     */
    public function __construct(string $name, Container $container, IO $io, PushTask $task)
    {
        $this->app = $container;
        parent::__construct($name, $io, $task);
    }

    /**
     * push
     *
     * @param int $fd
     * @param string $event
     * @param array $data
     * @return void
     */
    protected function push(int $fd, string $event, array $data = []): void
    {//[$fd, $event, $data]
        Dispatcher::dispatch($this->task, [$fd, $event, $data]);
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