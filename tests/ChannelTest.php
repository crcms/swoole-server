<?php

namespace CrCms\Server\Tests;

use CrCms\Server\WebSocket\Channel;
use CrCms\Server\WebSocket\IO;
use CrCms\Server\WebSocket\Rooms\RedisRoom;
use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Redis\Connectors\PredisConnector;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelTest.
 */
class ChannelTest extends TestCase
{
    /**
     * @var Channel
     */
    protected static $channel;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub

        $app = \Mockery::mock('Illuminate\Contracts\Container\Container');

        $redisRoom = new RedisRoom((new PredisConnection(
            (new PredisConnector())->connect([
                'host'     => 'redis',
                'password' => null,
                'port'     => 6379,
                'database' => 10,
            ], [])
        )));

        static::$channel = new Channel(
            new IO($app, $redisRoom), '/'
        );

        static::$channel->getIo()->getRoom()->reset();
    }

    public function testJoinAndRooms()
    {
        static::$channel->join(1, 'x1');
        static::$channel->join(1, 'x2');
        static::$channel->join(1, 'x3');
        static::$channel->join(1, ['x4', 'x5']);

        $result = static::$channel->rooms(1);
        $this->assertEquals(5, count($result));
        foreach ($result as $room) {
            $this->assertEquals(true,
                strpos($room, 'x1') ||
                strpos($room, 'x2') ||
                strpos($room, 'x3') ||
                strpos($room, 'x4') ||
                strpos($room, 'x5')
            );
        }
    }

    public function testTo()
    {
        $result = static::$channel->join(1, 'x1')->to('x1')->to('x2')->to(['x3', 1, 2, 3])->getTo();

        $this->assertEquals(3, count($result));
        foreach ($result as $value) {
            $this->assertEquals(true, in_array($value, [1, 2, 3]));
        }
    }

    public function testRemove()
    {
        static::$channel->join(2, 'x1');
        static::$channel->join(2, 'x2');
        static::$channel->join(2, 'x3');

        static::$channel->remove(2, ['x1']);

        $result = static::$channel->rooms(2);
        $this->assertEquals(2, count($result));
    }

//    public function testOnAndDispatch()
//    {
//        $listener = \Mockery::mock('Mocker\Listener');
//
//        $listener->shouldReceive('handle')->andReturn(true);
//
//        static::$channel::on('test','Mocker\Listener::class');
//        static::$channel->dispatch('test',[]);
//    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        static::$channel = null;
    }
}
