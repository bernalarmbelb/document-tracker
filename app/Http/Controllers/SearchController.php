<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Resolutions;
use App\Models\Ordinances;
use App\Models\Minutes;
use App\Models\Communications;

class SearchController extends Controller
{
    public $edit = FALSE;
    public $messages = array();
    public $search_title = "";
    public $search_keyword = "";
    public $search_author = "";
    public $search_series_number = "";
    public $search_start_date = "";
    public $search_end_date = "";
    public $search_resolution = "1";
    public $search_ordinance = "1";
    public $search_minute = "1";
    public $search_communication = "1";
    public $search_ordinance_types = [];

    // Session keys that make up the "remembered" search filters.
    protected $filter_keys = [
        'search_title', 'search_keyword', 'search_author', 'search_series_number',
        'search_start_date', 'search_end_date', 'search_resolution', 'search_ordinance',
        'search_minute', 'search_communication', 'search_ordinance_types',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {

            if (session()->has('search_title')) $this->search_title = session('search_title');
            if (session()->has('search_keyword')) $this->search_keyword = session('search_keyword');
            if (session()->has('search_author')) $this->search_author = session('search_author');
            if (session()->has('search_series_number')) $this->search_series_number = session('search_series_number');
            if (session()->has('search_start_date')) $this->search_start_date = session('search_start_date');
            if (session()->has('search_end_date')) $this->search_end_date = session('search_end_date');
            if (session()->has('search_resolution')) $this->search_resolution = session('search_resolution');
            if (session()->has('search_ordinance')) $this->search_ordinance = session('search_ordinance');
            if (session()->has('search_minute')) $this->search_minute = session('search_minute');
            if (session()->has('search_communication')) $this->search_communication = session('search_communication');
            if (session()->has('search_ordinance_types')) $this->search_ordinance_types = session('search_ordinance_types');

            return $next($request);
        });

        $this->middleware('permission:Search Document');
    }

    public function global_search(Request $request)
    {
        $data = $request->all();

        $this->search_title = trim($data['title'] ?? '');
        session(['search_title' => $this->search_title]);

        $this->search_keyword = trim($data['keyword'] ?? '');
        session(['search_keyword' => $this->search_keyword]);

        $this->search_author = trim($data['author'] ?? '');
        session(['search_author' => $this->search_author]);

        $this->search_series_number = trim($data['series_number'] ?? '');
        session(['search_series_number' => $this->search_series_number]);

        // If the staff member didn't tick a single document type, search
        // every type instead of silently returning nothing — most people
        // just type a keyword and hit Search without touching the checkboxes.
        $no_type_chosen = !isset($data['resolutions']) && !isset($data['ordinances'])
            && !isset($data['minutes']) && !isset($data['communications']);

        $this->search_resolution = $no_type_chosen ? '1' : ($data['resolutions'] ?? '0');
        session(['search_resolution' => $this->search_resolution]);

        $this->search_ordinance = $no_type_chosen ? '1' : ($data['ordinances'] ?? '0');
        session(['search_ordinance' => $this->search_ordinance]);

        $this->search_minute = $no_type_chosen ? '1' : ($data['minutes'] ?? '0');
        session(['search_minute' => $this->search_minute]);

        $this->search_communication = $no_type_chosen ? '1' : ($data['communications'] ?? '0');
        session(['search_communication' => $this->search_communication]);

        $this->search_start_date = $data['start_date'] ?? '';
        session(['search_start_date' => $this->search_start_date]);

        $this->search_end_date = $data['end_date'] ?? '';
        session(['search_end_date' => $this->search_end_date]);

        // Ordinance-type checkboxes are a sub-filter of "Ordinances" — if that
        // type isn't part of this search, ignore any leftover selection
        // (e.g. left checked, then hidden, from an earlier search).
        $ordinance_types_selected = ($no_type_chosen || isset($data['ordinances']))
            ? ($data['ordinance_types'] ?? [])
            : [];
        $this->search_ordinance_types = $ordinance_types_selected;
        session(['search_ordinance_types' => $this->search_ordinance_types]);

        $query1 = Resolutions::where("is_deleted", 0)->where("is_archived", 0);
        $query2 = Ordinances::where("is_deleted", 0)->where("is_archived", 0);
        $query3 = Minutes::where("is_deleted", 0)->where("is_archived", 0);
        $query4 = Communications::where("is_deleted", 0)->where("is_archived", 0);

        //SEARCH BY KEYWORD
        $keywords = $this->search_keyword;
        if ($keywords !== '') {
            $terms = explode(' ', $keywords);
            $query1->where(function ($q) use ($terms){
                foreach ($terms as $term)
                    $q->orWhere('keywords_tags', 'LIKE', '%' . $term . '%');
            });

            $query2->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('keywords_tags', 'LIKE', '%' . $term . '%');
            });

            $query3->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('short_description', 'LIKE', '%' . $term . '%');
            });

            $query4->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('particulars', 'LIKE', '%' . $term . '%');
            });
        }

        //SEARCH BY TITLE
        $keywords = $this->search_title;
        if ($keywords !== '') {
            $terms = explode(' ', $keywords);

            $query1->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('title', 'LIKE', '%' . $term . '%');
            });

            $query2->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('short_title', 'LIKE', '%' . $term . '%');
            });

            $query3->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('short_description', 'LIKE', '%' . $term . '%');
            });

            $query4->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('particulars', 'LIKE', '%' . $term . '%');
            });
        }

        //SEARCH BY AUTHOR
        $keywords = $this->search_author;
        if ($keywords !== '') {
            $terms = explode(' ', $keywords);
            $query1->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('author_name', 'LIKE', '%' . $term . '%');
            });

            $query2->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('author_name', 'LIKE', '%' . $term . '%');
            });

            $query3->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('presiding_officer', 'LIKE', '%' . $term . '%');
            });

            // Communications have no author/presiding-officer field.
            $query4->whereRaw('1 = 0');
        }

        //SEARCH BY SERIES NUMBER
        $keywords = $this->search_series_number;
        if ($keywords !== '') {
            $terms = explode(' ', $keywords);
            $query1->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('series_number', 'LIKE', '%' . $term . '%');
            });

            $query2->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('ordinance_number', 'LIKE', '%' . $term . '%');
            });

            $query3->where(function ($q) use ($terms) {
                foreach ($terms as $term)
                    $q->orWhere('series_number', 'LIKE', '%' . $term . '%');
            });

            // Communications have no series number.
            $query4->whereRaw('1 = 0');
        }

        //SEARCH BY START DATE
        $start_timestamp = $this->search_start_date !== '' ? strtotime($this->search_start_date) : false;
        if ($start_timestamp !== false) {
            $start_date = date("Y-m-d", $start_timestamp);
            $query1->whereRaw("date(date_created) >= ?", [$start_date]);
            $query2->whereRaw("date(date_created) >= ?", [$start_date]);
            $query3->whereRaw("date(date_created) >= ?", [$start_date]);
            $query4->whereRaw("date(created_at) >= ?", [$start_date]);
        }

        //SEARCH BY END DATE
        $end_timestamp = $this->search_end_date !== '' ? strtotime($this->search_end_date) : false;
        if ($end_timestamp !== false) {
            $end_date = date("Y-m-d", $end_timestamp);
            $query1->whereRaw("date(date_created) <= ?", [$end_date]);
            $query2->whereRaw("date(date_created) <= ?", [$end_date]);
            $query3->whereRaw("date(date_created) <= ?", [$end_date]);
            $query4->whereRaw("date(created_at) <= ?", [$end_date]);
        }

        //SEARCH BY ORDINANCE TYPE (narrows Ordinances only — never hides the other document types)
        if (!empty($ordinance_types_selected)) {
            $query2->where(function ($q) use ($ordinance_types_selected) {
                foreach ($ordinance_types_selected as $term)
                    $q->orWhere('ordinance_type', $term);
            });
        }

        $results1 = collect();
        $results2 = collect();
        $results3 = collect();
        $results4 = collect();

        if ($this->search_resolution == '1') $results1 = $query1->get();
        if ($this->search_ordinance == '1') $results2 = $query2->get();
        if ($this->search_minute == '1') $results3 = $query3->get();
        if ($this->search_communication == '1') $results4 = $query4->get();

        $filters_applied = array_filter([
            $this->search_title !== '' ? "Title contains \"{$this->search_title}\"" : null,
            $this->search_keyword !== '' ? "Keywords: \"{$this->search_keyword}\"" : null,
            $this->search_author !== '' ? "Author/Presiding Officer: \"{$this->search_author}\"" : null,
            $this->search_series_number !== '' ? "Series Number: \"{$this->search_series_number}\"" : null,
            $this->search_start_date !== '' ? "From: {$this->search_start_date}" : null,
            $this->search_end_date !== '' ? "To: {$this->search_end_date}" : null,
            !empty($ordinance_types_selected) ? "Ordinance Type: " . implode(', ', $ordinance_types_selected) : null,
        ]);

        $data = array(
            'menu' => 'Search',
            'resolutions' => $results1,
            'ordinances' => $results2,
            'minutes' => $results3,
            'communications' => $results4,
            'filters_applied' => $filters_applied,
            'result_count' => $results1->count() + $results2->count() + $results3->count() + $results4->count(),
        );

        return view("search_results", $data);
    }

    // Wipes the remembered filters so the next visit to the modal starts blank.
    public function clear(Request $request)
    {
        session()->forget($this->filter_keys);

        return redirect()->back();
    }

}
