<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Events\Internal;

use Illuminate\Http\Request;

/**
 * Class ConnectionHandledEvent.
 */
class ConnectionHandledEvent
{
    /**
     * @var Request
     */
    public $request;

    /**
     * ConnectionHandled constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
