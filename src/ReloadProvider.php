<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:42
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Server;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class ReloadProvider
 * @package CrCms\Server
 */
class ReloadProvider
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * ReloadProvider constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     */
    public function handle()
    {
        $providers = $this->app['config']->get('swoole.reload_providers');

        foreach ($providers as $provider) {
            $this->app->register($provider, true);
            $provider = $this->app->getProvider($provider);
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }
}