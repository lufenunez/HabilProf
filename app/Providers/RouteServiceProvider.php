<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define las rutas de la aplicación.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // Rutas API → tendrán prefijo /api/
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rutas web normales
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
