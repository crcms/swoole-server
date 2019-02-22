<?php

namespace CrCms\Server\Tests\Laravel;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\SwooleServiceProvider;
use Illuminate\Contracts\Container\Container;
use CrCms\Server\Drivers\Laravel\Application as BaseApplication;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;

class Application extends BaseApplication implements ApplicationContract
{
    /**
     * initialization
     *
     * @param string $basePath
     * @return void
     */
    protected function initialization(string $basePath): void
    {
        if (!file_exists($basePath.'/bootstrap/cache')) {
            mkdir($basePath.'/bootstrap/cache', 0777, true);
        }
        if (!file_exists($basePath.'/views')) {
            mkdir($basePath.'/views', 0777, true);
        }

        if (!file_exists($basePath.'/config')) {
            mkdir($basePath.'/config', 0777,  true);
        }
        file_put_contents($basePath.'/config/app.php', '<?php'.PHP_EOL.var_export([], true).';');
    }

    protected function createApplication(): Container
    {
        $basePath = __DIR__.'/../tmp/laravel';

        $this->initialization($basePath);

        $app = new LaravelApplication(
            $basePath
        );
        //$app->instance('path.config',__DIR__.'/../../config');
        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Illuminate\Foundation\Http\Kernel::class
        );
        $app->singleton(
            Kernel::class,
            \Illuminate\Foundation\Console\Kernel::class
        );
        $app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        $this->bootstrap($app);

        $providers = [
//            \Illuminate\Auth\AuthServiceProvider::class,
            //Illuminate\Broadcasting\BroadcastServiceProvider::class,
//            \Illuminate\Cache\CacheServiceProvider::class,
//            \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
//            \Illuminate\Cookie\CookieServiceProvider::class,
//            \Illuminate\Database\DatabaseServiceProvider::class,
//            \Illuminate\Encryption\EncryptionServiceProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
//            Illuminate\Hashing\HashServiceProvider::class,
//            Illuminate\Mail\MailServiceProvider::class,
//            \Illuminate\Notifications\NotificationServiceProvider::class,
            \Illuminate\Pagination\PaginationServiceProvider::class,
            \Illuminate\Pipeline\PipelineServiceProvider::class,
            \Illuminate\Queue\QueueServiceProvider::class,
            \Illuminate\Redis\RedisServiceProvider::class,
//            \Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
//            \Illuminate\Session\SessionServiceProvider::class,
//            \Illuminate\Translation\TranslationServiceProvider::class,
//            \Illuminate\Validation\ValidationServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
            SwooleServiceProvider::class,
        ];

        $app->make('config')->set(['view.paths' => [$basePath.'/views'] ]);
        $app->make('config')->set(['view.compiled' => [$basePath.'/views'] ]);

//
        $providers = array_map(function ($provider) use ($app) {
            return new $provider($app);
        }, $providers);

// register
        foreach ($providers as $provider) {
            $provider->register();
        }

// boot
        foreach ($providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
//        $app->make('config')->set(['swoole' => require __DIR__.'/../../config/config.php' ]);

        return $app;

    }
}