<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:41
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Listeners;

use CrCms\Microservice\Server\Events\RequestHandled;
use CrCms\Server\ReloadProvider;

/**
 * Class ServiceHandledListener
 * @package CrCms\Server\Listeners
 */
class CrCmsRequestHandledListener
{
    /**
     * @var ReloadProvider
     */
    protected $reloadProvider;

    /**
     * CrCmsRequestHandledListener constructor.
     * @param ReloadProvider $reloadProvider
     */
    public function __construct(ReloadProvider $reloadProvider)
    {
        $this->reloadProvider = $reloadProvider;
    }

    /**
     * @param $event
     */
    public function handle(RequestHandled $event)
    {
        $this->reloadProvider->handle();
    }
}