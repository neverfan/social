<?php

namespace App\Providers;

use App\Support\Response\Response;
use Illuminate\Support\ServiceProvider;
use App\Support\Response\Interfaces\ApiResponseInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ApiResponseInterface::class, Response::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
