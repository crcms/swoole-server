<?php

namespace CrCms\Server\Server;

use BadMethodCallException;
use CrCms\Server\Server\Contracts\ServerActionContract;
use CrCms\Server\Server\Contracts\ServerContract;
use CrCms\Server\WebSocket\Tasks\PushTask;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Swoole\Process;
use Swoole\Server as SwooleServer;
use Swoole\Server;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class AbstractServer.
 */
abstract class AbstractServer
{
    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $events = [
        'start' => \CrCms\Server\Server\Events\StartEvent::class,
        'worker_start' => \CrCms\Server\Server\Events\WorkerStartEvent::class,
        'close' => \CrCms\Server\Server\Events\CloseEvent::class,
        'task' => \CrCms\Server\Server\Events\TaskEvent::class,
        'finish' => \CrCms\Server\Server\Events\FinishEvent::class,
        'manager_start' => \CrCms\Server\Server\Events\ManagerStartEvent::class,
    ];

    /**
     * @var array
     */
    protected $eventObjects = [];

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct(Server $server, array $config)
    {
        $this->config = $config;
        $this->server = $server;
    }

    abstract public function name(): string;

    public function create2()
    {

    }

    abstract public function create(): SwooleServer;

    public function start()
    {
        $this->mergeConfig();

        $this->server->set($this->settings);
        $this->eventRegister();

        $this->server->start();
    }

    protected function mergeConfig()
    {
        $this->settings = array_merge($this->settings, $this->config['settings'] ?? []);
        $this->events = array_merge($this->events, $this->config['events'] ?? []);
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

    protected function setSettings(): void
    {
        $this->server->set($this->settings);
    }

    /**
     * @return void
     */
    protected function eventRegister(): void
    {
        Collection::make($this->events)->each(function (string $event, string $name) {
            $name = Str::camel($name);
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
     * @param array $arguments
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
