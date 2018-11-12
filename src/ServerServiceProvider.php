<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:26
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server;

use Illuminate\Support\ServiceProvider;

/**
 * Class ServerServiceProvider
 * @package CrCms\Server
 */
class ServerServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $packagePath = __DIR__ . '/../';

    /**
     * @var string
     */
    protected $name = 'server';

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            $this->packagePath . 'config' => config_path($this->name . '.php'),
        ]);
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->packagePath . "config/config.php", $this->name
        );
    }
}