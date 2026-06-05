<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS kalau bukan local (Render selalu HTTPS di belakang proxy).
        // Mencegah form action http:// → browser "not secure" warning.
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Use Tailwind for pagination
        Paginator::useTailwind();
    }
}
