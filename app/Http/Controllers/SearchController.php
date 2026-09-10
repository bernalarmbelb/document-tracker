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

    // Display preferences — remembered across visits like the filters below,
    // but never wiped by "clear filters" (they're not part of $filter_keys).
    public $view = 'grid';
    public $per_page = 12;
    // Default: newest series number first. Series numbers reset every year,
    // so "number" already sorts year-then-number (see seriesSortValue()) —
    // there's no reliable date to fall back on since date_created is often
    // identical across a whole batch of same-session documents.
    public $sort_col = 'number';
    public $sort_dir = 'desc';

    const SORTABLE_COLUMNS = ['type', 'title', 'number', 'author', 'date'];

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
            if (session()->has('search_view')) $this->view = session('search_view');
            if (session()->has('search_per_page')) $this->per_page = session('search_per_page');
            if (session()->has('search_sort_col')) $this->sort_col = session('search_sort_col');
            if (session()->has('search_sort_dir')) $this->sort_dir = session('search_sort_dir');

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

        [$results1, $results2, $results3, $results4, $filters_applied] = $this->buildFilteredResults();

        return $this->renderResults($request, $results1, $results2, $results3, $results4, $filters_applied, true);
    }

    // GET endpoint used by pagination links, the rows-per-page input, and the
    // Grid/List toggle — re-runs the search already stored in session without
    // touching the stored filters, so paging never resets what was searched.
    public function results(Request $request)
    {
        [$results1, $results2, $results3, $results4, $filters_applied] = $this->buildFilteredResults();

        return $this->renderResults($request, $results1, $results2, $results3, $results4, $filters_applied, false);
    }

    // Builds the four filtered collections from the currently-loaded
    // search_* properties (populated either from this request's POST data or,
    // for pagination/view GET requests, from session by the constructor).
    protected function buildFilteredResults()
    {
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
        if (!empty($this->search_ordinance_types)) {
            $query2->where(function ($q) {
                foreach ($this->search_ordinance_types as $term)
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
            !empty($this->search_ordinance_types) ? "Ordinance Type: " . implode(', ', $this->search_ordinance_types) : null,
        ]);

        return [$results1, $results2, $results3, $results4, $filters_applied];
    }

    // Merges the four typed collections into one ordered list — same relative
    // order as before (all resolutions, then ordinances, then minutes, then
    // communications) — so paging doesn't change what shows compared to the
    // old un-paginated masonry, it just slices it. Handles the Grid/List and
    // rows-per-page preferences (remembered in session) and the current page.
    protected function renderResults(Request $request, $results1, $results2, $results3, $results4, $filters_applied, bool $isNewSearch)
    {
        if ($request->query('view') !== null) {
            $this->view = $request->query('view') === 'list' ? 'list' : 'grid';
            session(['search_view' => $this->view]);
        }

        if ($request->query('per_page') !== null) {
            $requested = (int) $request->query('per_page');
            $this->per_page = $requested > 0 ? max(4, min(200, $requested)) : $this->per_page;
            session(['search_per_page' => $this->per_page]);
        }

        if ($request->query('sort') !== null) {
            $col = $request->query('sort');
            if (in_array($col, self::SORTABLE_COLUMNS, true)) {
                $this->sort_col = $col;
                session(['search_sort_col' => $this->sort_col]);
            }
        }

        if ($request->query('dir') !== null) {
            $this->sort_dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';
            session(['search_sort_dir' => $this->sort_dir]);
        }

        $merged = [];
        foreach ($results1 as $item) $merged[] = $this->mergedRow('resolution', $item);
        foreach ($results2 as $item) $merged[] = $this->mergedRow('ordinance', $item);
        foreach ($results3 as $item) $merged[] = $this->mergedRow('minutes', $item);
        foreach ($results4 as $item) $merged[] = $this->mergedRow('communication', $item);

        $col = $this->sort_col;
        $dir = $this->sort_dir;
        usort($merged, function ($a, $b) use ($col, $dir) {
            $va = $a->sort[$col];
            $vb = $b->sort[$col];
            // Records with no real value for this column (e.g. communications
            // have no series number) always sort last, in either direction —
            // "Newest No. First" shouldn't push them to the top just because
            // their placeholder value happens to be the largest.
            if ($va === null && $vb === null) return 0;
            if ($va === null) return 1;
            if ($vb === null) return -1;
            $cmp = is_string($va) ? strcasecmp($va, $vb) : ($va <=> $vb);
            return $dir === 'desc' ? -$cmp : $cmp;
        });

        $total = count($merged);
        $perPage = $this->per_page;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = $isNewSearch ? 1 : max(1, (int) $request->query('page', 1));
        $page = min($page, $lastPage);

        $items = array_slice($merged, ($page - 1) * $perPage, $perPage);

        return view("search_results", [
            'menu' => 'Search',
            'items' => $items,
            'filters_applied' => $filters_applied,
            'result_count' => $total,
            'view' => $this->view,
            'per_page' => $perPage,
            'page' => $page,
            'last_page' => $lastPage,
            'page_list' => $this->buildPageList($page, $lastPage),
            'sort_col' => $this->sort_col,
            'sort_dir' => $this->sort_dir,
        ]);
    }

    // One merged row plus its normalized, type-independent sort keys — so
    // clicking "Title"/"No."/"Author"/"Date" in the list view can sort across
    // all four document types consistently.
    protected function mergedRow(string $type, $item): object
    {
        switch ($type) {
            case 'resolution':
                $sort = [
                    'type' => $type, 'title' => strtolower($item->title ?? ''),
                    'number' => $this->seriesSortValue($item->series_number ?? ''),
                    'author' => strtolower($item->author_name ?? ''),
                    'date' => strtotime($item->date_created ?? '') ?: 0,
                ];
                break;
            case 'ordinance':
                $sort = [
                    'type' => $type, 'title' => strtolower($item->short_title ?? ''),
                    'number' => $this->seriesSortValue($item->ordinance_number ?? ''),
                    'author' => strtolower($item->author_name ?? ''),
                    'date' => strtotime($item->date_created ?? '') ?: 0,
                ];
                break;
            case 'minutes':
                $sort = [
                    'type' => $type, 'title' => strtolower(trim(($item->presiding_officer ?? '') . ' ' . ($item->barangay_name ?? ''))),
                    'number' => $this->seriesSortValue($item->series_number ?? ''),
                    'author' => '',
                    'date' => strtotime($item->date_created ?? '') ?: 0,
                ];
                break;
            default: // communication
                $sort = [
                    'type' => $type, 'title' => strtolower($item->particulars ?? ''),
                    'number' => null, // communications have no series number
                    'author' => strtolower(($item->communication_type ?? '') === 'INCOMING' ? ($item->received_by ?? '') : ($item->released_by ?? '')),
                    'date' => strtotime($item->created_at ?? '') ?: 0,
                ];
        }

        return (object) ['type' => $type, 'item' => $item, 'sort' => $sort];
    }

    // Series numbers are "NUMBER-YEAR" (resolutions/minutes) or "YEAR-NUMBER"
    // (ordinances) and the counter resets every year, so sort by year first,
    // then number within that year — same convention as the DataTables
    // "tm-series" sort type used on the list/archive pages. Returns null
    // (always sorts last, see renderResults()) when there's nothing to parse.
    protected function seriesSortValue($text)
    {
        $text = trim((string) $text);
        if ($text === '') return null;

        preg_match_all('/\d+/', $text, $m);
        $groups = $m[0];
        if (count($groups) < 2) {
            return is_numeric($text) ? (float) $text : null;
        }

        $yearIdx = null;
        foreach ($groups as $i => $g) {
            if (strlen($g) === 4) { $yearIdx = $i; break; }
        }

        if ($yearIdx !== null) {
            $year = (int) $groups[$yearIdx];
            $number = null;
            foreach ($groups as $i => $g) {
                if ($i !== $yearIdx) { $number = (int) $g; break; }
            }
        } else {
            // No clean 4-digit year (e.g. a truncated "066-20") — best
            // effort, assume the more common NUMBER-YEAR order.
            $number = (int) $groups[0];
            $year = (int) $groups[1];
        }

        return ($year * 100000) + $number;
    }

    // Flat pager with ellipsis, matching the treasury DataTables pager:
    // first, last, current, and the pages immediately around current.
    protected function buildPageList(int $current, int $total): array
    {
        $keep = array_values(array_unique(array_filter(
            [1, $total, $current, $current - 1, $current + 1],
            fn ($p) => $p >= 1 && $p <= $total
        )));
        sort($keep);

        $list = [];
        $prev = 0;
        foreach ($keep as $p) {
            if ($prev && $p - $prev > 1) $list[] = '…';
            $list[] = $p;
            $prev = $p;
        }

        return $list;
    }

    // Wipes the remembered filters so the next visit to the modal starts blank.
    public function clear(Request $request)
    {
        session()->forget($this->filter_keys);

        return redirect()->back();
    }

}
