<?php

namespace CrCms\Server\Tests\Laravel\Http;

use CrCms\Server\Drivers\Laravel\Http\Events\RequestEvent;
use CrCms\Server\Drivers\Laravel\Http\Server;
use CrCms\Server\Tests\Laravel\ApplicationTrait;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    use ApplicationTrait;

    public function testServer()
    {
        $server = new Server(
            static::$app->make('config')->get('swoole'),
            static::$app->make('server.laravel')
        );

        $server->newServer();

        $server = \Mockery::mock($server);
        $server->shouldReceive('start');


        $server->start();

        $this->assertInstanceOf(RequestEvent::class,$server->getObjectEventOrException('request'));
    }

}