<?php

namespace CrCms\Server\Tests\Room;

use CrCms\Server\WebSocket\Rooms\RedisRoom;
use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Redis\Connectors\PredisConnector;
use PHPUnit\Framework\TestCase;

/**
 * Class RedisRoomTest
 */
class RedisRoomTest extends TestCase
{
    /**
     * @var RedisRoom
     */
    protected $room;

    /**
     * @var PredisConnection
     */
    protected $redis;

    public function setup()
    {
        parent::setup();

        $this->redis = (new PredisConnection(
            (new PredisConnector())->connect([
                'host' => 'redis',
                'password' => null,
                'port' =>  6379,
                'database' => 10,
            ],[])
        ));

        $this->room = new RedisRoom(
            $this->redis
        );
    }

    public function testAdd()
    {
        $this->room->add(1,'room1');
        $this->room->add(2,'room1');
        $this->room->add(3,'room1');

        $result = $this->redis->smembers('room1');

        $this->assertEquals(1,intval($result[0]));
        $this->assertEquals(2,intval($result[1]));
        $this->assertEquals(3,intval($result[2]));

        $this->room->add(20,['room2','room3']);


        $result2 = $this->redis->smembers('room2');
        $result3 = $this->redis->smembers('room3');

        $this->assertEquals(20,intval($result2[0]));
        $this->assertEquals(20,intval($result3[0]));
    }

    public function testGet()
    {
        $this->room->add(10,'room1');
        $this->room->add(11,'room2');
        $this->room->add(12,'room3');

        foreach ($this->room->get(['room1','room2']) as $value) {
            $this->assertEquals(true,$value == 10 || $value == 11);
        }

        $this->assertEquals(2,count($this->room->get(['room1','room2'])));
    }

    public function testAll()
    {
        $this->room->add(1,'xroom1');
        $this->room->add(2,'xroom1');
        $this->room->add(3,'xroom2');

        $this->assertEquals(3,count($this->room->all()));
    }

    public function testRemove()
    {
        $this->room->add(1, 'zroom1');
        $this->room->add(2, 'zroom2');
        $this->room->add(3, ['zroom2', 'zoom3']);

        $this->room->remove(1);
        $this->room->remove(2);

        $result = $this->redis->smembers('zroom1');
        $this->assertEquals(0,count($result));

        $result = $this->redis->smembers('zroom2');
        $this->assertEquals(1,count($result));


        $this->room->remove(3,'zoom3');
        $result = $this->redis->smembers('zoom3');
        $this->assertEquals(0, count($result));

        $this->room->remove(3,'zroom2');
        $result = $this->redis->smembers('zroom2');
        $this->assertEquals(0, count($result));
    }

    public function tearDown()
    {
        $this->redis->flushall();
        $this->room = $this->redis = null;
    }
}