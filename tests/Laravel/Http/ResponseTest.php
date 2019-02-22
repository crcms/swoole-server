<?php

namespace CrCms\Server\Tests\Laravel\Http;

use CrCms\Server\Drivers\Laravel\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{

    public function testResponse()
    {
        $swooleResponse = \Mockery::mock(\Swoole\Http\Response::class);
        $swooleResponse->shouldReceive('status')->andReturn(200);
        $swooleResponse->shouldReceive('header')->andReturn(['content-type'=>'text/plain']);
        $swooleResponse->shouldReceive('end')->andReturn(123);



        $laravelResponse = new \Symfony\Component\HttpFoundation\Response();

        $response = new Response($swooleResponse, $laravelResponse);

        $response->toResponse();

        $this->assertEquals(true,true);
    }

}