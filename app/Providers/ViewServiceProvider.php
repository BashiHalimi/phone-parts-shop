<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Share alert counts with the admin navbar and sidebar.
        View::composer([
            'admin.partials.navbar',
            'admin.partials.sidebar',
            'admin.notifications.*',
        ], function ($view) {
            if (! Auth::check()) {
                return;
            }

            // Only admins/staff need these; customers won't load admin views anyway.
            $lowStockCount   = Product::whereColumn('quantity', '<=', 'minimum_stock')
                ->where('quantity', '>', 0)
                ->count();
            $outOfStockCount = Product::where('quantity', '<=', 0)->count();
            $totalAlerts     = $lowStockCount + $outOfStockCount;

            $view->with([
                'navLowStockCount'   => $lowStockCount,
                'navOutOfStockCount' => $outOfStockCount,
                'navTotalAlerts'     => $totalAlerts,
            ]);
        });
    }

    public function register(): void
    {
        //
    }
}