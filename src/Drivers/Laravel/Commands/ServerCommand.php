<?php

namespace CrCms\Server\Drivers\Laravel\Commands;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\Laravel;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\ServerManager;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use OutOfBoundsException;
use Swoole\Server as SwooleServer;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;
use Throwable;

/**
 * Class ServerCommand.
 */
class ServerCommand extends Command implements ApplicationContract
{
    /**
     * @var string
     */
    protected $signature = 'server {name : Configure the key of the `servers` array} {action : start or stop or restart or reload} {--daemon}';

    /**
     * @var array
     */
    protected $allows = ['start', 'stop', 'restart', 'reload'];

    /**
     * @var string
     */
    protected $description = 'Swoole server command';


    public function handle(): void
    {
        $this->info('
                                    ____               
                                  ,\'  , `.             
           __  ,-.             ,-+-,.\' _ |             
         ,\' ,\'/ /|          ,-+-. ;   , ||  .--.--.    
   ,---. \'  | |\' | ,---.   ,--.\'|\'   |  || /  /    \'   
  /     \|  |   ,\'/     \ |   |  ,\', |  |,|  :  /`./   
 /    / \'\'  :  / /    / \' |   | /  | |--\' |  :  ;_     
.    \' / |  | \' .    \' /  |   : |  | ,     \  \    `.  
\'   ; :__;  : | \'   ; :__ |   : |  |/       `----.   \ 
\'   | \'.\'|  , ; \'   | \'.\'||   | |`-\'       /  /`--\'  / 
|   :    :---\'  |   :    :|   ;/          \'--\'.     /  
 \   \  /        \   \  / \'---\'             `--\'---\'   
  `----\'          `----\'                               
        
        ');

        try {
            $action = $this->argument('action');

            $name = $this->argument('name');

            $driver = $this->getServerDriver($name);

            $server = new $driver(
                $this->getServerConfig($name),
                new Laravel($this)
            );

            if ($action === 'info') {
                $action = 'serverInfo';
            }
            call_user_func([$this, $action], new ServerManager($server));
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            $this->info($e->getTraceAsString());
        }
    }

    /**
     * Start a server
     *
     * @param ServerManager $manager
     * @return void
     */
    protected function start(ServerManager $manager)
    {
        $baseConfig = $manager->getServer()->getBaseConfig();

        $phpversion = phpversion();
        $swooleversion = swoole_version();
        $string = <<<str
Host:   {$baseConfig['host']}
Port:   {$baseConfig['port']}
Server: {$baseConfig['driver']}
Swoole: {$swooleversion}
PHP:    {$phpversion}
str;
        $this->table(['Name', 'Info'], [
            ['Host', $baseConfig['host']],
            ['Port', $baseConfig['port']],
            ['Server', $baseConfig['driver']],
            ['Laravel', '5.7 [<info>'.env('APP_ENV').'</info>]'],//$this->laravel->version().
            ['Swoole', swoole_version()],
            ['PHP', phpversion()],
        ]);

        if ($this->option('daemon')) {
            $this->comment('Server runs as a daemon, please use ps aux | grep swoole to view the process'.PHP_EOL);
        }

        //$this->info($string);

        $manager->start();
    }

    protected function serverInfo(ServerManager $manager)
    {
        $baseConfig = $manager->getServer()->getBaseConfig();

        $this->table(['Name', 'Info'], [
            ['Host', $baseConfig['host']],
            ['Port', $baseConfig['port']],
            ['Pid', $manager->getPid()],
            ['PidFile', $manager->getServer()->getSetting('pid_file')],
            ['Status', $manager->processExists() ? 'Running' : 'Stopping'],
            ['Server', $baseConfig['driver']],
            ['Laravel', '5.7 [<info>'.env('APP_ENV').'</info>]'],//$this->laravel->version().
            ['Swoole', swoole_version()],
            ['PHP', phpversion()],
        ]);
    }

    /**
     * Get current swoole server type
     *
     * @param AbstractServer $server
     * @return string
     */
    protected function getServerType(AbstractServer $server): string
    {
        $swoole = $server->getServer();

        if ($swoole instanceof WebSocketServer) {
            return 'websocket';
        } elseif ($swoole instanceof HttpServer) {
            return 'http';
        } elseif ($swoole instanceof SwooleServer) {
            return 'tcp|udp';
        } else {
            return 'unknown';
        }
    }

    /**
     * Get current server settings
     *
     * @param string $name
     * @return array
     */
    protected function getServerConfig(string $name): array
    {
        $config = $this->laravel->make('config')->get('swoole', []);

        if ($this->option('daemon')) {
            $config["swoole.servers.{$name}.settings.daemonize"] = true;
        }

        return $config;
    }

    /**
     * @return void
     */
    protected function cleanRunCache(): void
    {
        (new Filesystem())->cleanDirectory(
            dirname($this->getLaravel()->getCachedServicesPath())
        );
    }

    public static function application(): \Illuminate\Contracts\Container\Container
    {
        $container = Container::getInstance();
        $container->bind('config', function () {
            return new \Illuminate\Config\Repository(['swoole' => require __DIR__.'/../../../../config/config.php']);
        });
        return $container;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getServerDriver(string $name): string
    {
        $servers = $this->laravel->make('config')->get('swoole.servers', []);

        if (in_array($name, array_keys($servers), true)) {
            return $this->laravel->make('config')->get("swoole.servers.{$name}.driver");
        }

        throw new OutOfBoundsException("The server type: [{$name}] not found");
    }
}
