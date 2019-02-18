<?php

namespace CrCms\Server\Tests\Laravel;

use CrCms\Server\Drivers\Laravel\Commands\ServerCommand;
use PHPUnit\Framework\TestCase;

class ServerCommandTest extends TestCase
{

    public function testCase()
    {
        $application = new \Symfony\Component\Console\Application();

        $application->add(new ServerCommand());

        $application->run();

        dd(123);
    }

}