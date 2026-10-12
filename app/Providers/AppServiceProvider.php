<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Builder::macro('like', function (string $attribute, string $searchTerm) {
            return $this->where($attribute, 'LIKE', "%{$searchTerm}%");
        });

        \Illuminate\Database\Query\Builder::macro('like', function (string $attribute, string $searchTerm) {
            return $this->where($attribute, 'LIKE', "%{$searchTerm}%");
        });
    }
}
