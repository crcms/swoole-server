<?php

namespace CrCms\Server\Server;

use BadMethodCallException;
use CrCms\Server\Server\Events\AbstractEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Swoole\Server as SwooleServer;
use OutOfRangeException;
use Swoole\Server;

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
        $this->server = $this->create();
        $this->setSettings();
        $this->eventRegister();

        return $this;
    }

    /**
     * name
     *
     * @return string
     */
    abstract public function name(): string;

    /**
     * create
     *
     * @return Server
     */
    abstract public function create(): SwooleServer;

    /**
     * setSettings
     *
     * @return void
     */
    protected function setSettings(): void
    {
        $this->server->set($this->settings);
    }

    /**
     * mergeSettings
     *
     * @return void
     */
    protected function mergeSettings(): void
    {
        $this->settings = array_merge($this->settings, $this->baseConfig['settings'] ?? []);
        if (empty($this->settings['pid_file'])) {
            $this->settings['pid_file'] = $this->pidFile();
        }
    }

    /**
     * getSettings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * getEvents
     *
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * getObjectEvents
     *
     * @return array
     */
    public function getObjectEvents(): array
    {
        return $this->eventObjects;
    }

    /**
     * Get current server one setting
     * Throws an exception if the key does not exist
     *
     * @param string $key
     * @return string|int|null
     */
    public function getSettingOrException(string $key)
    {
        if (!isset($this->settings[$key])) {
            throw new OutOfRangeException("The setting: {$key} not exists");
        }

        return $this->settings[$key];
    }

    /**
     * Get current server one setting
     * Allow custom default value
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Get current server event object
     *
     * @param string $event
     * @return AbstractEvent
     */
    public function getObjectEventOrException(string $event): AbstractEvent
    {
        if (!isset($this->eventObjects[$event])) {
            throw new OutOfRangeException("The event: {$event} not exists");
        }

        return $this->eventObjects[$event];
    }

    /**
     * getBaseConfig
     *
     * @return array
     */
    public function getBaseConfig(): array
    {
        if (empty($this->config['servers'][$this->name])) {
            throw new OutOfRangeException("The server[{$this->name}] not exists");
        }

        return $this->config['servers'][$this->name];
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
     * pidFile
     *
     * @return string
     */
    protected function pidFile(): string
    {
        return '/var/'.$this->name.'.pid';
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
                    $this->eventObjects[$name] = new $event($this, ...$this->filterServer(func_get_args()));
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
        return array_filter($args, function ($item) {
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
