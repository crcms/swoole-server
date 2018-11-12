<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018-04-02 20:58
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class ManagerStartEvent
 * @package CrCms\Server\Server\Events
 */
class ManagerStartEvent extends AbstractEvent implements EventContract
{
    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        parent::setEventProcessName('manage');
    }
}