<?php

namespace CrCms\Server\Server;

use BadMethodCallException;
use CrCms\Server\Server\Contracts\ServerActionContract;
use CrCms\Server\Server\Contracts\ServerContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Swoole\Process;
use Swoole\Server as SwooleServer;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class AbstractServer.
 */
abstract class AbstractServer implements ServerActionContract, ServerContract
{
    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $defaultEvents = [
        'start'         => \CrCms\Server\Server\Events\StartEvent::class,
        'worker_start'  => \CrCms\Server\Server\Events\WorkerStartEvent::class,
        'worker_stop'   => '',
        'worker_exit'   => '',
        'connect'       => '',
        'receive'       => '',
        'packet'        => '',
        'close'         => \CrCms\Server\Server\Events\CloseEvent::class,
        'buffer_full'   => '',
        'Buffer_empty'  => '',
        'task'          => \CrCms\Server\Server\Events\TaskEvent::class,
        'finish'        => \CrCms\Server\Server\Events\FinishEvent::class,
        'pipe_message'  => '',
        'worker_error'  => '',
        'manager_start' => \CrCms\Server\Server\Events\ManagerStartEvent::class,
        'manager_stop'  => '',
    ];

    /**
     * @var array
     */
    protected $eventObjects = [];

    /**
     * @var array
     */
    protected $defaultSettings = [
        'package_max_length' => 1024 * 1024 * 10,
        'user'               => 'daemon',
        'group'              => 'daemon',
    ];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct(string $name,array $config)
    {
        $this->config = $config;
        $this->name = $name;
    }

    /**
     * @param string $name
     *
     * @return AbstractServer
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
//    public function start(): bool
//    {
//        return $this->server->start();
//    }
//
//    /**
//     * @return bool
//     */
//    public function stop(): bool
//    {
//        $this->server->shutdown();
//
//        return true;
//    }
//
//    /**
//     * @return bool
//     */
//    public function restart(): bool
//    {
//        return $this->server->reload();
//    }

    /**
     * @return string
     */
//    public function pidFile(): string
//    {
//        return $this->config['settings']['pid_file'] ?? '';
//    }

    /**
     * @return SwooleServer
     */
    public function getServer(): SwooleServer
    {
        return $this->server;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $settings
     *
     * @return void
     */
    protected function setSettings(array $settings): void
    {
        $this->server->set(array_merge($this->defaultSettings, $this->settings, $settings));
    }

    /**
     * @return void
     */
    protected function eventDispatcher(array $events): void
    {
        Collection::make(array_merge($this->defaultEvents, $this->events, $events))->filter(function (string $event) {
            return class_exists($event);
        })->each(function (string $event, string $name) {
            $this->eventsCallback(Str::camel($name), $event);
        });
    }

    /**
     * @param string $name
     * @param string $event
     *
     * @return void
     */
    protected function eventsCallback(string $name, string $event): void
    {
        $this->server->on($name, function () use ($name, $event) {
            try {
                $this->eventObjects[$name] = new $event(...$this->filterServer(func_get_args()));
                $this->eventObjects[$name]->run($this);
            } catch (\Exception $e) {
                //$this->app->make(ExceptionHandler::class)->report($e);
                //log
                throw $e;
            } catch (\Throwable $e) {
                //$this->app->make(ExceptionHandler::class)->report(new FatalThrowableError($e));
                //log
                throw $e;
            }
        });
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function filterServer(array $args): array
    {
        return Collection::make($args)->filter(function ($item) {
            return !($item instanceof \Swoole\Server);
        })->toArray();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->eventObjects)) {
            return $this->eventObjects[$name];
        }

        if (isset($this->server->{$name})) {
            return $this->server->{$name};
        }

        throw new InvalidArgumentException("The attributes[{$name}] is not exists");
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->server, $name)) {
            return $this->server->{$name}(...$arguments);
        }

        throw new BadMethodCallException("The method:[{$name}] not exists");
    }
}
