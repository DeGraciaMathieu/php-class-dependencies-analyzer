<?php

namespace App\Providers;

use App\Core\Parser\ClassParser;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(ClassParser::class, PhpParserClassParser::class);
    }
}
