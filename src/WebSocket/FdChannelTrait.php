<?php

namespace CrCms\Server\WebSocket;

/**
 * Trait FdChannelTrait.
 */
trait FdChannelTrait
{
    /**
     * @return Channel
     */
    protected function currentChannel(IO $io, int $fd): Channel
    {
        $channels = $io->getChannels();

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

        throw new \RangeException('The channel not found');
    }
}
