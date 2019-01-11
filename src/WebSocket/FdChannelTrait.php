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
     * @return string
     */
    protected function channelName(int $fd): string
    {
        $channels = IO::getChannels();

        $currentChannel = null;

        /* @var Channel $channel */
        foreach ($channels as $channel) {
            $rooms = $channel->rooms($fd);
            foreach ($rooms as $room) {
                if ($room === $channel->getName() . '_' . strval($fd)) {
                    $currentChannel = $channel;
                    break;
                }
            }

            if ($currentChannel) {
                break;
            }
        }

        if (empty($currentChannel)) {
            throw new \RangeException("The channel not found");
        }

        return $currentChannel->getName();
    }
}