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
        // Share webSettings globally with all views
        view()->composer('*', function ($view) {
            $settingsPath = storage_path('app/settings.json');
            $webSettings = [
                'nama_restoran' => 'Kopi Premium',
                'logo' => null,
                'pajak' => 10,
                'footer' => 'Terima kasih atas kunjungan Anda!',
                'bahasa' => 'id',
            ];
            if (file_exists($settingsPath)) {
                $saved = json_decode(file_get_contents($settingsPath), true);
                if (is_array($saved)) {
                    $webSettings = array_merge($webSettings, $saved);
                }
            }
            $view->with('webSettings', $webSettings);
        });
    }
}
