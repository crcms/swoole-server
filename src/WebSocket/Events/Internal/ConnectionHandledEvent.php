<?php

namespace CrCms\Server\WebSocket\Events\Internal;

use Illuminate\Http\Request;

/**
 * Class ConnectionHandledEvent
 * @package CrCms\Server\WebSocket\Internal
 */
class ConnectionHandledEvent
{
    /**
     * @var Request
     */
    public $request;

    /**
     * ConnectionHandled constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}