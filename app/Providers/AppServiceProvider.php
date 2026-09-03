<?php

namespace App\Providers;

use App\Services\BureauCreditoClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // O cliente do Bureau depende de configuração (URL e timeout), então
        // é registrado explicitamente em vez de resolvido por autowiring.
        $this->app->singleton(
            BureauCreditoClient::class,
            fn () => BureauCreditoClient::apartirDaConfiguracao(),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
