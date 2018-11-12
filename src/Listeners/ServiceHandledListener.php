<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:41
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Listeners;

use CrCms\Microservice\Server\Events\ServiceHandled;
use CrCms\Server\ReloadProvider;

/**
 * Class ServiceHandledListener
 * @package CrCms\Server\Listeners
 */
class ServiceHandledListener
{
    /**
     * @var ReloadProvider
     */
    protected $reloadProvider;

    /**
     * RequestHandledListener constructor.
     * @param ReloadProvider $reloadProvider
     */
    public function __construct(ReloadProvider $reloadProvider)
    {
        $this->reloadProvider = $reloadProvider;
    }

    /**
     * @param $event
     */
    public function handle(ServiceHandled $event)
    {
        $this->reloadProvider->handle();
    }
}