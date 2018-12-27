<?php

namespace CrCms\Server\WebSocket;

use Illuminate\Http\Request;

/**
 * Class ConnectionHandled
 * @package CrCms\Server\WebSocket
 */
class ConnectionHandled
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