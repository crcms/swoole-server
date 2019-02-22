<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\Drivers\Laravel\WebSocket\Tasks\PushTask;
use CrCms\Server\WebSocket\Contracts\RoomContract;
use OutOfRangeException;

abstract class AbstractChannel
{
    /**
     * @var IO
     */
    protected $io;

    /**
     * @var RoomContract
     */
    protected $room;

    /**
     * @var array
     */
    protected $to = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var PushTask
     */
    protected $task;

    /**
     * @param string $name
     * @param IO $io
     * @param PushTask $task
     */
    public function __construct(string $name, IO $io, PushTask $task)
    {
        $this->name = $name;
        $this->task = $task;
        $this->io = $io;
        $this->room = $io->getRoom();
    }

    /**
     * register event
     *
     * @param $event
     * @param $listener
     * @return void
     */
    public function on($event, $listener): void
    {
        $this->events[$event] = $listener;
    }

    /**
     * @param $event
     *
     * @return bool
     */
    public function eventExists($event): bool
    {
        return isset($this->events[$event]);
    }

    /**
     * @param $event
     */
    public function dispatch($event, array $data = []): void
    {
        if (!$this->eventExists($event)) {
            throw new OutOfRangeException("The event[{$event}] not found");
        }

        $this->call($this->events[$event], $data);
    }

    /**
     * @param Socket $socket
     * @param $room
     *
     * @return self
     */
    public function join(int $fd, $room): self
    {
        $this->room->add($fd, $this->filterGetRooms($room));

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
     * @return IO
     */
    public function getIo(): IO
    {
        return $this->io;
    }

    /**
     * @param string|array $room
     */
    public function to($room): self
    {
        $this->to = array_unique(array_merge($this->to, $this->room->get($this->filterGetRooms($room)), $this->filterGetFds($room)));

        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param $event
     * @param mixed $data
     */
    public function emit($event, $data = [])
    {
        /*if (empty($this->to)) {
            $this->to = $this->room->all();
        }*/

        foreach ($this->to as $to) {
            $this->push(intval($to), $event, $data);
        }

        $this->reset();
    }

    /**
     * @param int $fd
     */
    public function remove(int $fd, $room = []): void
    {
        $this->room->remove($fd, $this->filterGetRooms($room ? $room : '*'));
    }

    /**
     * @param int $fd
     *
     * @return array
     */
    public function rooms(int $fd): array
    {
        $rooms = $this->room->keys($fd);

        $prefix = $this->channelPrefix();

        return array_filter($rooms, function (string $room) use ($prefix) {
            return strpos($room, $prefix) !== false;
        });
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->to = [];
    }

    /**
     * @return string
     */
    public function channelPrefix(): string
    {
        return $this->name.'_';
    }

    /**
     * emit data
     *
     * @param PushTask $task
     * @param int $fd
     * @param string $event
     * @param array $data
     * @return void
     */
    abstract protected function push(int $fd, string $event, array $data = []): void;

    /**
     * call event
     *
     * @param $call
     * @param array $data
     * @return mixed
     */
    abstract protected function call($call, array $data = []): void;

    /**
     * @param $room
     *
     * @return array
     */
    protected function filterGetFds($room): array
    {
        return array_filter((array)$room, function ($value) {
            return is_int($value);
        });
    }

    /**
     * @param $room
     *
     * @return array
     */
    protected function filterGetRooms($room): array
    {
        $prefix = $this->channelPrefix();

        return array_map(function ($room) use ($prefix) {
            return $prefix.$room;
        }, array_filter((array)$room, function ($value) {
            return !is_int($value);
        }));
    }
}
