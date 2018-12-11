<?php

namespace CrCms\Server\Server;

use CrCms\Server\Server\Contracts\ServerActionContract;
use CrCms\Server\Server\Contracts\ServerContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Swoole\Process;
use Swoole\Server as SwooleServer;
use BadMethodCallException;

/**
 * Class AbstractServer
 * @package CrCms\Server\Server
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
        'start' => \CrCms\Server\Server\Events\StartEvent::class,
        'worker_start' => \CrCms\Server\Server\Events\WorkerStartEvent::class,
        'worker_stop' => '',
        'worker_exit' => '',
        'connect' => '',
        'receive' => '',
        'packet' => '',
        'close' => \CrCms\Server\Server\Events\CloseEvent::class,
        'buffer_full' => '',
        'Buffer_empty' => '',
        'task' => \CrCms\Server\Server\Events\TaskEvent::class,
        'finish' => \CrCms\Server\Server\Events\FinishEvent::class,
        'pipe_message' => '',
        'worker_error' => '',
        'manager_start' => \CrCms\Server\Server\Events\ManagerStartEvent::class,
        'manager_stop' => '',
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
        'daemonize' => true,
        'user' => 'daemon',
        'group' => 'daemon',
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
     * @var Process
     */
    protected $process;

    /**
     * @var string
     */
    protected $name;

    /**
     * AbstractServer constructor.
     * @param Container $app
     * @param array $config
     * @param null|string $name
     */
    public function __construct(Container $app, array $config, string $name)
    {
        $this->app = $app;
        $this->config = $config;
        $this->name = $name;
        $this->bindInstanceToApplication();
    }

    /**
     * @return void
     */
    abstract public function bootstrap(): void;

    /**
     * @param string $name
     * @return AbstractServer
     */
    public function setName(string $name): AbstractServer
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
    public function start(): bool
    {
        return $this->server->start();
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        $this->server->shutdown();
        return true;
    }

    /**
     * @return bool
     */
    public function restart(): bool
    {
        return $this->server->reload();
    }

    /**
     * @return string
     */
    public function pidFile(): string
    {
        if (empty($this->config['settings']['pid_file'])) {
            $this->config['settings']['pid_file'] = storage_path($this->name . '.pid');
        }

        return $this->config['settings']['pid_file'];
    }

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
     * @return Container
     */
    public function getApp(): Container
    {
        return $this->app;
    }

    /**
     * @return Container
     */
    public function getApplication(): Container
    {
        return $this->getApp();
    }

    /**
     * @param array $settings
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
     * @return void
     */
    protected function eventsCallback(string $name, string $event): void
    {
        $this->server->on($name, function () use ($name, $event) {
            $this->eventObjects[$name] = new $event(...$this->filterServer(func_get_args()));
            $this->eventObjects[$name]->handle($this);
        });
    }

    /**
     * @param array $args
     * @return array
     */
    protected function filterServer(array $args): array
    {
        return Collection::make($args)->filter(function ($item) {
            return !($item instanceof \Swoole\Server);
        })->toArray();
    }

    /**
     * @return void
     */
    protected function bindInstanceToApplication(): void
    {
        $this->app->instance('server', $this);
    }

    /**
     * @param string $name
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
     * @param array $arguments
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