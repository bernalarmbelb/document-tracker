<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        // Quick-Create bar counts — shared with the main layout on every authed page.
        View::composer('template', function ($view) {
            $counts = ['res_pending' => 0, 'ord_pending' => 0, 'min_month' => 0, 'com_month' => 0, 'act_upcoming' => 0];

            if (Auth::check()) {
                try {
                    $y = Carbon::now()->year;
                    $m = Carbon::now()->month;
                    $counts['res_pending']  = \App\Models\Resolutions::where('is_deleted', 0)->where('is_archived', 0)->where('resolution_status', '!=', 'APPROVED')->count();
                    $counts['ord_pending']  = \App\Models\Ordinances::where('is_deleted', 0)->where('is_archived', 0)->where('ordinance_status', '!=', 'APPROVED')->count();
                    $counts['act_upcoming'] = \App\Models\SangguniangActivities::where('is_deleted', 0)->where('status', 'UPCOMING')->count();
                    $counts['min_month']    = \App\Models\Minutes::where('is_deleted', 0)->whereYear('date_created', $y)->whereMonth('date_created', $m)->count();
                    $counts['com_month']    = \App\Models\Communications::where('is_deleted', 0)->whereYear('created_at', $y)->whereMonth('created_at', $m)->count();
                } catch (\Throwable $e) {
                    // never break page render over the quick bar
                }
            }

            $view->with('quick_counts', $counts);
        });
    }
}
