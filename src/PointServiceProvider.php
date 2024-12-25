<?php

namespace WalkerChiu\Point;

use Illuminate\Support\ServiceProvider;

class PointServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/point.php' => config_path('wk-point.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_point_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_point_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-point');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-point'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-point.command.cleaner')
            ]);
        }

        config('wk-core.class.point.setting')::observe(config('wk-core.class.point.settingObserver'));
        config('wk-core.class.point.settingLang')::observe(config('wk-core.class.point.settingLangObserver'));
        config('wk-core.class.point.wallet')::observe(config('wk-core.class.point.walletObserver'));
        config('wk-core.class.point.log')::observe(config('wk-core.class.point.logObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-point')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/point.php', 'wk-point'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/point.php', 'point'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
