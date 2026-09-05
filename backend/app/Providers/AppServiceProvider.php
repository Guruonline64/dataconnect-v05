<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DataProvider;
use App\Services\VtuNgProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DataProvider::class, VtuNgProvider::class);
    }
    public function boot(): void {}
}
