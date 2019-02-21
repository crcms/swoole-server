<?php

namespace CrCms\Server\WebSocket\Contracts;

use CrCms\Server\WebSocket\IO;

interface BindingIOContract
{
    /**
     * setIO
     *
     * @param IO $io
     * @return mixed
     */
    public function setIO(IO $io);

    /**
     * getIO
     *
     * @return IO
     */
    public function getIO(): IO;
}