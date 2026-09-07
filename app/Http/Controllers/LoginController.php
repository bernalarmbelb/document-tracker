<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

use App\Models\Resolutions;
use App\Models\Ordinances;
use App\Models\Minutes;
use App\Models\Communications;
use App\Models\SangguniangActivities;
use App\Models\ActivityLogs;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::user()) 
        {                   
            return redirect("/dashboard");
        }
        return view('login_form');
    }

    public function check_login(Request $request)
    {
        $username = (string) $request->input('username');
        $throttleKey = 'login:'.Str::lower($username).'|'.$request->ip();

        // Brute-force protection: block after 5 failed attempts (per username+IP).
        if (RateLimiter::tooManyAttempts($throttleKey, 5))
        {
            $mins = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            log_activity('User Login (Throttled)', json_encode(["username" => $username, "ip" => $request->ip()]));
            return redirect('/login')->with('error', "Too many failed attempts. Please try again in about {$mins} minute(s).");
        }

        $credentials = array(
            'username' => $username,
            'password' => $request->input('password'),
        );

        if (Auth::attempt($credentials))
        {
           if(Auth::user()->status=='INACTIVE')
           {
                log_activity('User Login (Inactive)', json_encode(["username"=> $username]));
                Auth::logout();
                return redirect('/login')->with('error', 'Account is Inactive.');
           }
           RateLimiter::clear($throttleKey);
           $request->session()->regenerate();   // prevent session fixation
           log_activity('User Login', json_encode(["username"=> $username]));
           return redirect("/dashboard");
        }

        RateLimiter::hit($throttleKey, 300);     // record failure, 5-minute decay
        return redirect('/login')->with('error', 'Wrong Username or Password');
    }

    public function dashboard()
    {        
        log_activity('View Dashboard');
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $all_resolutions = Resolutions::where("is_deleted", 0)->where("is_archived", 0)               
                ->selectRaw("id, series_number, author_name, title, keywords_tags, date_created, approved_date, resolution_status as status, 'RESOLUTION' as record_type")
                ->get();

        $all_ordinances = Ordinances::where("is_deleted", 0)->where("is_archived", 0)               
                ->selectRaw("id, ordinance_number as series_number, author_name, short_title as title, keywords_tags, date_created, approved_date, ordinance_status as status, 'ORDINANCE' as record_type")
                ->get();  
                
        $merged  = $all_resolutions->concat($all_ordinances);

        // ---- Additional read-only dashboard data (no writes) ----
        $minutes_ctr        = Minutes::where(['is_deleted' => 0])->count();
        $communications_ctr = Communications::where(['is_deleted' => 0])->count();
        $activities_ctr     = SangguniangActivities::where(['is_deleted' => 0])->count();

        // Calendar of Events widget — small dataset, fetch once and let the client
        // bucket by date (avoids relying on the manually-set `status` column, which
        // can go stale once an activity's date has passed).
        $calendar_activities = SangguniangActivities::where(['is_deleted' => 0, 'is_archived' => 0])
            ->orderBy('activity_date', 'asc')
            ->get(['id', 'activity_title', 'activity_date', 'status', 'location', 'duration']);

        // Documents created per month, for the most recent year that has data
        // (falls back to the current year when there are no documents yet).
        $latestResYear = Resolutions::where('is_deleted', 0)->max(DB::raw('YEAR(date_created)'));
        $latestOrdYear = Ordinances::where('is_deleted', 0)->max(DB::raw('YEAR(date_created)'));
        $trend_year = (int) max($latestResYear ?? 0, $latestOrdYear ?? 0) ?: $currentYear;

        $resByMonth = Resolutions::where('is_deleted', 0)
            ->whereYear('date_created', $trend_year)
            ->selectRaw('MONTH(date_created) m, COUNT(*) c')->groupBy('m')->pluck('c', 'm');
        $ordByMonth = Ordinances::where('is_deleted', 0)
            ->whereYear('date_created', $trend_year)
            ->selectRaw('MONTH(date_created) m, COUNT(*) c')->groupBy('m')->pluck('c', 'm');
        $monthly_labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthly_counts = collect(range(1, 12))
            ->map(fn ($m) => (int) ($resByMonth[$m] ?? 0) + (int) ($ordByMonth[$m] ?? 0))->all();

        // All documents (resolutions + ordinances), newest first — paginated in the view via DataTables
        $documents = $merged->sortByDesc(fn ($r) => $r->date_created)->values();

        // ---- Analytics (computed in-memory from $merged; no extra DB queries) ----
        // Top authors by combined resolution + ordinance count.
        $top_authors = $merged
            ->filter(fn ($r) => trim((string) $r->author_name) !== '')
            ->groupBy('author_name')
            ->map(fn ($g) => $g->count())
            ->sortDesc()
            ->take(6);

        // Key insights: volume this year vs last, this month, and approval turnaround.
        $docs_this_year  = $merged->filter(fn ($r) => (int) date('Y', strtotime($r->date_created)) === (int) $currentYear)->count();
        $docs_last_year  = $merged->filter(fn ($r) => (int) date('Y', strtotime($r->date_created)) === (int) $currentYear - 1)->count();
        $docs_this_month = $merged->filter(fn ($r) => date('Y-m', strtotime($r->date_created)) === date('Y-m'))->count();

        $approvedDurations = $merged
            ->filter(fn ($r) => strtoupper((string) $r->status) === 'APPROVED'
                && !empty($r->approved_date) && strtotime($r->approved_date) && strtotime($r->date_created)
                && strtotime($r->approved_date) >= strtotime($r->date_created))
            ->map(fn ($r) => (strtotime($r->approved_date) - strtotime($r->date_created)) / 86400);
        $avg_approval_days = $approvedDurations->count() ? (int) round($approvedDurations->avg()) : null;

        // Activity history feed (latest 12, with username)
        $activity_feed = ActivityLogs::leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->orderByDesc('activity_logs.activity_date')
            ->limit(12)
            ->get(['activity_logs.action', 'activity_logs.activity_date', 'users.username']);

        $data = array(
            'menu' => 'Dashboard',              
            'resolutions_ctr' => Resolutions::where(['is_deleted' => '0', 'is_archived' => '0'])->count(),
            'resolutions_approved_ctr' => Resolutions::where(['is_deleted' => '0', 'is_archived' => '0', 'resolution_status' => 'APPROVED'])->count(),
            'resolutions_disapproved_ctr' => Resolutions::where(['is_deleted' => '0', 'is_archived' => '0', 'resolution_status' => 'DISAPPROVED'])->count(),
            'resolutions_abeyance_ctr' => Resolutions::where(['is_deleted' => '0', 'is_archived' => '0', 'resolution_status' => 'IN ABEYANCE'])->count(),
            'resolutions_no_text_ctr' => Resolutions::where(['is_deleted' => '0', 'is_archived' => '0', 'resolution_status' => 'NO TEXT PROVIDED'])->count(),
            'resolutions_under_study_ctr' => Resolutions::where(['is_deleted' => '0', 'is_archived' => '0', 'resolution_status' => 'UNDER STUDY'])->count(),
            'ordinances_ctr' => Ordinances::where(['is_deleted' => '0', 'is_archived' => '0'])->count(),
            'ordinances_approved_ctr' => Ordinances::where(['is_deleted' => '0', 'is_archived' => '0', 'ordinance_status' => 'APPROVED'])->count(),
            'ordinances_invalid_ctr' => Ordinances::where(['is_deleted' => '0', 'is_archived' => '0', 'ordinance_status' => 'INVALID'])->count(),
            'ordinances_reports_ctr' => Ordinances::where(['is_deleted' => '0', 'is_archived' => '0', 'ordinance_status' => 'REPORTS'])->count(),
            'ordinances_under_study_ctr' => Ordinances::where(['is_deleted' => '0', 'is_archived' => '0', 'ordinance_status' => 'UNDER STUDY'])->count(),
            'all_resolutions' =>  $merged,
            'minutes_ctr' => $minutes_ctr,
            'communications_ctr' => $communications_ctr,
            'activities_ctr' => $activities_ctr,
            'calendar_activities' => $calendar_activities,
            'monthly_labels' => $monthly_labels,
            'monthly_counts' => $monthly_counts,
            'trend_year' => $trend_year,
            'documents' => $documents,
            'activity_feed' => $activity_feed,
            'top_authors' => $top_authors,
            'docs_this_year' => $docs_this_year,
            'docs_last_year' => $docs_last_year,
            'docs_this_month' => $docs_this_month,
            'avg_approval_days' => $avg_approval_days,
        );
        return view("dashboard", $data);
    }   

    public function logout(Request $request)
    {
        log_activity('User Logout');
        Auth::logout();
    
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/login');
    }

}
