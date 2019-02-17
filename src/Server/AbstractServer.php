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
use OutOfRangeException;
use Swoole\Server;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Swoole\Http\Server as HttpServer;

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
    protected $baseConfig;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $events = [
        'start' => \CrCms\Server\Server\Events\StartEvent::class,
        'worker_start' => \CrCms\Server\Server\Events\WorkerStartEvent::class,
        'task' => \CrCms\Server\Server\Events\TaskEvent::class,
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
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->name = $this->name();
        $this->baseConfig = $this->getBaseConfig();
        $this->mergeSettings();
    }

    /**
     * create
     *
     * @return SwooleServer
     */
    public function newServer(): AbstractServer
    {
        //return $server = new HttpServer($config['host'], $config['port'], $mode, $type);

//        $serverParams = [
//            $this->baseConfig['host'],
//            $this->baseConfig['port'],
//            $this->baseConfig['mode'] ?? SWOOLE_PROCESS,
//            $this->baseConfig['type'] ?? SWOOLE_SOCK_TCP,
//        ];
//
//        $server = new $this->baseConfig['driver'](...$serverParams);
//        $this->setSettings($server);
//        $this->eventRegister($server);
//
//        return $server;

        $this->server = $this->create();
        $this->setSettings();
        $this->eventRegister();

        return $this;
    }


    abstract public function name(): string;

    abstract public function create(): SwooleServer;


    public function start()
    {
        $this->newServer();
        $this->server->start();
    }

    protected function setSettings(): void
    {
        $this->server->set($this->settings);
    }

    protected function mergeSettings(): void
    {
        $this->settings = array_merge($this->settings, $this->baseConfig['settings'] ?? []);
        if (empty($this->settings['pid_file'])) {
            $this->settings['pid_file'] = $this->pidFile();
        }
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getBaseConfig(): array
    {
        if (empty($this->config['servers'][$this->name])) {
            throw new OutOfRangeException("The server[{$this->name}] not exists");
        }

        return $this->config['servers'][$this->name];
    }

    protected function pidFile(): string
    {
        return '/var/'.$this->name.'.pid';
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
     * @return void
     */
    protected function eventRegister(): void
    {
        Collection::make($this->events)->each(function (string $event, string $name) {
            $name = Str::camel($name);
            $this->server->on($name, function () use ($name, $event) {
                try {
                    $this->eventObjects[$name] = new $event($this,...$this->filterServer(func_get_args()));
                    $this->eventObjects[$name]->handle();
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
        return array_filter($args,function($item){
            return !($item instanceof \Swoole\Server);
        });
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
