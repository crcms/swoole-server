<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Tasks;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\TaskContract;
use CrCms\Server\WebSocket\Contracts\ConverterContract;
use CrCms\Server\WebSocket\Contracts\ParserContract;
use OutOfBoundsException;

/**
 * Class PushTask.
 */
final class PushTask implements TaskContract
{
    /**
     * @var ParserContract
     */
    protected $parser;

    /**
     * @var ConverterContract
     */
    protected $converter;

    /**
     * @param ParserContract $parser
     * @param ConverterContract $converter
     */
    public function __construct(ParserContract $parser, ConverterContract $converter)
    {
        $this->parser = $parser;
        $this->converter = $converter;
    }

    /**
     * @param mixed ...$params
     *
     * @return mixed|void
     */
    public function handle(...$params): void
    {
        /* @var AbstractServer $server */
        $server = array_shift($params);
        /* @var int $fd */
        $fd = array_shift($params);
        /* @var array $data */
        $data = $this->parser->pack(
            $this->converter->conversion($params)
        );

        if ($server->getServer()->isEstablished($fd)) {
            $server->getServer()->push($fd, $data);

            return;
        }

        throw new OutOfBoundsException("The fd:[{$fd}] not websocket or websocket close");
    }

    /**
     * @param $data
     *
     * @return mixed|void
     */
    public function finish($data)
    {
    }
}
