<?php

use CrCms\Server\WebSocket\Facades\IO;
use CrCms\Server\WebSocket\Socket;

IO::of('/')->on('connection', function (Socket $socket) {

});

IO::of('/')->on('message', function (Socket $socket) {

});

IO::of('/')->on('disconnection', function (Socket $socket) {

});