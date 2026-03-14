<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $txtLocale = config('app.locale');

        Carbon::setLocale($txtLocale);
        setlocale(
            LC_TIME,
            'es_CO.UTF-8',
            'es_CO',
            'Spanish_Colombia.1252',
            'es_ES.UTF-8',
            'es_ES',
            'es'
        );
    }
}
