<?php

namespace CrCms\Server\Server;


use CrCms\Server\Server\Contracts\ServerContract;

class EventDispatcher
{

    public static function dispatch(ServerContract $server, array $events)
    {

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
}