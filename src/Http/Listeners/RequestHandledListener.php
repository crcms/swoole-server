<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:41
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server\Http\Listeners;

use CrCms\Server\ReloadProvider;

/**
 * Class RequestHandled
 * @package CrCms\Server\Listeners
 */
class RequestHandledListener
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
    public function handle($event)
    {
        $this->reloadProvider->handle();
    }
}