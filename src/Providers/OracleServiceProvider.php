<?php
/**
 * Created by PhpStorm.
 * User: darlane
 * Date: 22.05.18
 * https://github.com/darlane
 */

namespace OracleLib\Providers;


use Illuminate\Support\ServiceProvider;
use OracleLib\Base\Connection;
use OracleLib\Repositories\ExampleRepository;
use OracleLib\Repositories\OracleBaseRepository;


class OracleServiceProvider extends ServiceProvider
{
    use OracleBaseRepository;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('oracle', function () {
            return new Connection(config('oracle'));
        });

        $this->app->singleton(ExampleRepository::class, function () {
            return new ExampleRepository(app('oracle'));
        });

        $this->publishes([
            __DIR__.'/../../config/oracle.php' => config_path('oracle.php'),
        ], 'config');

        require_once __DIR__.'/../functions.php';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}