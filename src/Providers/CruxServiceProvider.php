<?php

namespace Railroad\Crux\Providers;

use Illuminate\Support\ServiceProvider;

class CruxServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/crux.php');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'crux');

        $this->mergeConfigFrom(__DIR__ . '/../../config/crux.php', 'crux');
    }
}