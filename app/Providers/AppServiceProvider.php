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
        // Konfigurasi khusus jika berjalan di lingkungan Vercel
        if (env('APP_ENV') === 'production') {
            $paths = [
                '/tmp/storage/framework/views',
                '/tmp/storage/framework/cache',
                '/tmp/storage/framework/sessions',
                '/tmp/bootstrap/cache',
            ];

            foreach ($paths as $path) {
                if (! is_dir($path)) {
                    mkdir($path, 0755, true);
                }
            }

            // Atur ulang path runtime Laravel ke folder /tmp
            config(['view.compiled' => '/tmp/storage/framework/views']);
            config(['cache.stores.file.path' => '/tmp/storage/framework/cache']);
            config(['session.files' => '/tmp/storage/framework/sessions']);
        }
    }
}
