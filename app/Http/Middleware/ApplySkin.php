<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApplySkin — sumbu desain kedua (MODERN | CLASSIC), server-side via cookie.
 *
 * MODERN  = desain asli (resources/views/...). Default, tidak disentuh.
 * CLASSIC = Claude Design. Saat aktif, folder resources/views/classic/
 *           di-PREPEND ke view finder, sehingga view('dashboard.gm')
 *           otomatis resolve ke classic/dashboard/gm.blade.php BILA ADA,
 *           dan FALLBACK ke view modern bila belum di-port.
 *
 * Tidak ada controller / view modern yang perlu diubah.
 */
class ApplySkin
{
    public function handle(Request $request, Closure $next): Response
    {
        $skin = $request->cookie('crm-skin') === 'classic' ? 'classic' : 'modern';

        if ($skin === 'classic') {
            View::getFinder()->prependLocation(resource_path('views/classic'));
        }

        // tersedia di semua view sebagai $skin + untuk @if(skin_is('classic'))
        View::share('skin', $skin);
        app()->instance('crm.skin', $skin);

        return $next($request);
    }
}
