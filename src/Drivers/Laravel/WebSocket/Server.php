<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket;

use CrCms\Server\Drivers\Laravel\Http\Server as HttpServer;
use CrCms\Server\WebSocket\Contracts\BindingIOContract;
use CrCms\Server\WebSocket\IO;

class Server extends HttpServer implements BindingIOContract
{
    /**
     * @var
     */
    protected $io;

    /**
     * setIO
     *
     * @param IO $io
     * @return mixed|void
     */
    public function setIO(IO $io)
    {
        $this->io = $io;
    }

    /**
     * getIO
     *
     * @return IO
     */
    public function getIO(): IO
    {
        return $this->io;
    }
}