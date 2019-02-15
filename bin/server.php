<?php
require __DIR__ . '/../vendor/autoload.php';
$command = new \CrCms\Server\Commands\ServerCommand();
var_dump($command->run());
//$manage = new \CrCms\Server\Server\ServerManager();
//
//$manage->run($command,new \CrCms\Server\Http\Server());