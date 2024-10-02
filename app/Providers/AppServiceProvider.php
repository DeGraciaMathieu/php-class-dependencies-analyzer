<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Ports\Repositories\FileRepository;
use App\Infrastructure\Repositories\FileRepositoryAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FileRepository::class, FileRepositoryAdapter::class);
    }
}
