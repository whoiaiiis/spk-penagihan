<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        // Custom Blade Directives for permissions
        \Illuminate\Support\Facades\Blade::directive('permission', function ($expression) {
            return "<?php if (has_permission($expression)): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });

        // Laravel Gate integration for @can support
        \Illuminate\Support\Facades\Gate::before(function ($user = null, $ability) {
            if (has_permission($ability)) {
                return true;
            }
            return null; // Let other gates check or return false
        });
    }
}