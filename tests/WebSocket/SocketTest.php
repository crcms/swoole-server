<?php

namespace CrCms\Server\WebSocket\Tests;

use CrCms\Server\WebSocket\AbstractChannel;
use CrCms\Server\WebSocket\Socket;
use PHPUnit\Framework\TestCase;

/**
 * Class SocketTest.
 */
class SocketTest extends TestCase
{
    /**
     * @var Socket
     */
    protected static $socket;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $channel = \Mockery::mock(AbstractChannel::class);
        $channel->shouldReceive('join')->andReturn($channel);
        $channel->shouldReceive('to')->andReturn($channel);
        $channel->shouldReceive('getTo')->andReturn([1]);
        $channel->shouldReceive('reset');
        $channel->shouldReceive('remove');
        static::$socket = new Socket($channel, 1);
        static::$socket->setData(['x'=>1])->setFd(1);
    }

    public function testJoin()
    {
        static::$socket->join(['x1', 'x2', 'x3']);

        $result = static::$socket->getChannel()->to(['x1', 'x2', 'x3'])->getTo();

        $this->assertEquals(1, count($result));
        foreach ($result as $value) {
            $this->assertEquals(true, in_array($value, [1]));
        }
    }

    /**
     * @depends testJoin
     */
    public function testLeave()
    {
        $channel = static::$socket->getChannel();
        $channel->reset();
        $result = $channel->to(['x1'])->getTo();
        $this->assertEquals(1, count($result));

        static::$socket->leave(['x1']);

        $channel->reset();
        $result = $channel->to(['x1'])->getTo();
        $this->assertEquals(0, count($result));
        $channel->reset();
        $result = $channel->to(['x2', 'x3'])->getTo();

        $this->assertEquals(1, count($result));
        $channel->reset();
        static::$socket->leave();
        $result = $channel->to(['x1', 'x2', 'x3'])->getTo();

        $this->assertEquals(0, count($result));
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        static::$socket = null;
    }
}
