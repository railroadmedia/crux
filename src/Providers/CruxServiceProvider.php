<?php

namespace Railroad\Crux\Providers;

class CruxServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/crux.php');

        $this->loadViewsFrom('/../../resources/views', 'crux');
    }
}