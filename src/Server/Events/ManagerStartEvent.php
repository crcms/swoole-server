<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018-04-02 20:58
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Server\Events;

/**
 * Class ManagerStartEvent.
 */
class ManagerStartEvent extends AbstractEvent
{
    /**
     * handle
     *
     * @return void
     */
    public function handle(): void
    {
        parent::setEventProcessName('manager');
    }
}
