<?php

namespace CrCms\Server\WebSocket;

use CrCms\Server\WebSocket\Facades\IO;

/**
 * Trait FdChannelTrait
 * @package CrCms\Server\WebSocket
 */
trait FdChannelTrait
{
    /**
     * @return Channel
     */
    protected function currentChannel(int $fd): Channel
    {
        $channels = IO::getChannels();

        $currentChannel = null;

        /* @var Channel $channel */
        foreach ($channels as $channel) {
            $rooms = $channel->rooms($fd);
            foreach ($rooms as $room) {
                if ($room === $channel->channelPrefix().strval($fd)) {
                    return $channel;
                }
            }
        }

        throw new \RangeException("The channel not found");
    }
}