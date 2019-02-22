<?php

namespace CrCms\Server\Drivers\Laravel\Commands;

use function CrCms\Server\Drivers\Laravel\get_framework_type;
use function CrCms\Server\Drivers\Laravel\get_framework_version;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\ServerManager;
use Illuminate\Console\Command;
use OutOfBoundsException;
use Swoole\Server as SwooleServer;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * Class ServerCommand.
 */
class ServerCommand extends Command
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

    /**
     * handle
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $action = $this->argument('action');

            $name = $this->argument('name');

            $driver = $this->getServerDriver($name);

            $server = new $driver(
                $this->getServerConfig($name),
                $this->laravel->make('server.laravel')
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
        $this->info($this->logo());

        $baseConfig = $manager->getServer()->getBaseConfig();

        $this->table(['Name', 'Info'], [
            ['Host', $baseConfig['host']],
            ['Port', $baseConfig['port']],
            ['Server', $baseConfig['driver']],
            ['Framework', get_framework_type($this->laravel).' ['.get_framework_version($this->laravel).']'],
            ['Swoole', swoole_version()],
            ['PHP', phpversion()],
            ['System Load', implode(',', sys_getloadavg())],
        ]);

        if ($this->option('daemon')) {
            $this->comment('Server runs as a daemon, please use ps aux | grep swoole to view the process'.PHP_EOL);
        }

        $manager->start();
    }

    /**
     * serverInfo
     *
     * @param ServerManager $manager
     * @return void
     */
    protected function serverInfo(ServerManager $manager): void
    {
        $this->info($this->logo());

        $baseConfig = $manager->getServer()->getBaseConfig();

        $this->table(['Name', 'Info'], [
            ['Host', $baseConfig['host']],
            ['Port', $baseConfig['port']],
            ['Pid', $manager->getPid()],
            ['PidFile', $manager->getServer()->getSetting('pid_file')],
            ['Status', $manager->processExists() ? 'Running' : 'Stopping'],
            ['Server', $baseConfig['driver']],
            ['Framework', get_framework_type($this->laravel).' ['.get_framework_version($this->laravel).']'],
            ['Swoole', swoole_version()],
            ['PHP', phpversion()],
            ['System Load', implode(',', sys_getloadavg())],
        ]);
    }

    /**
     * restart
     *
     * @param ServerManager $manager
     * @return void
     */
    public function restart(ServerManager $manager): void
    {
        $this->comment("The server restarted");

        $manager->restart();
    }

    /**
     * stop
     *
     * @param ServerManager $manager
     * @return void
     */
    protected function stop(ServerManager $manager): void
    {
        $manager->stop();

        $this->comment("The server stopped");
    }

    /**
     * reload
     *
     * @param ServerManager $manager
     * @return void
     */
    protected function reload(ServerManager $manager): void
    {
        $manager->reload();

        $this->comment("The server has been reloaded");
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
            $config['servers'][$name]['settings']['daemonize'] = true;
        }

        return $config;
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

    /**
     * logo
     *
     * @return string
     */
    protected function logo(): string
    {
        return <<<str
        
                                    ____                                                                                  
                                  ,'  , `.                                                                                
           __  ,-.             ,-+-,.' _ |                ,---,.                       __  ,-.                    __  ,-. 
         ,' ,'/ /|          ,-+-. ;   , ||  .--.--.     ,'  .' | .--.--.             ,' ,'/ /|    .---.         ,' ,'/ /| 
   ,---. '  | |' | ,---.   ,--.'|'   |  || /  /    '  ,---.'   ,/  /    '     ,---.  '  | |' |  /.  ./|  ,---.  '  | |' | 
  /     \|  |   ,'/     \ |   |  ,', |  |,|  :  /`./  |   |    |  :  /`./    /     \ |  |   ,'.-' . ' | /     \ |  |   ,' 
 /    / ''  :  / /    / ' |   | /  | |--' |  :  ;_    :   :  .'|  :  ;_     /    /  |'  :  / /___/ \: |/    /  |'  :  /   
.    ' / |  | ' .    ' /  |   : |  | ,     \  \    `. :   |.'   \  \    `. .    ' / ||  | '  .   \  ' .    ' / ||  | '    
'   ; :__;  : | '   ; :__ |   : |  |/       `----.   \`---'      `----.   \'   ;   /|;  : |   \   \   '   ;   /|;  : |    
'   | '.'|  , ; '   | '.'||   | |`-'       /  /`--'  /          /  /`--'  /'   |  / ||  , ;    \   \  '   |  / ||  , ;    
|   :    :---'  |   :    :|   ;/          '--'.     /          '--'.     / |   :    | ---'      \   \ |   :    | ---'     
 \   \  /        \   \  / '---'             `--'---'             `--'---'   \   \  /             '---" \   \  /           
  `----'          `----'                                                     `----'                     `----'            


str;
    }
}
