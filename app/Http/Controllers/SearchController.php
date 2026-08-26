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

            if(Auth::user()->account_type!="ADMINISTRATOR" && Auth::user()->account_type!="SUPER ADMIN") return redirect('/dashboard');  
            else return $next($request);          
        });           
    }

    public function global_search(Request $request)
    {          
        $data = $request->all();        
        
        $this->search_title = $data['title'];       
        session(['search_title' => $this->search_title]);   

        $this->search_keyword = $data['keyword'];       
        session(['search_keyword' => $this->search_keyword]);   

        $this->search_author = $data['author'];       
        session(['search_author' => $this->search_author]);   

        $this->search_series_number = $data['series_number'];       
        session(['search_series_number' => $this->search_series_number]);   

        $this->search_resolution = $data['resolutions'] ?? 0;       
        session(['search_resolution' => $this->search_resolution]);   

        $this->search_ordinance = $data['ordinances'] ?? 0;       
        session(['search_ordinance' => $this->search_ordinance]);   

        $this->search_minute = $data['minutes'] ?? 0;       
        session(['search_minute' => $this->search_minute]);   

        $this->search_communication = $data['communications'] ?? 0;       
        session(['search_communication' => $this->search_communication]);   

        $this->search_start_date = $data['start_date'] ?? '';       
        session(['search_start_date' => $this->search_start_date]);   

        $this->search_end_date = $data['end_date'] ?? '';       
        session(['search_end_date' => $this->search_end_date]);   

        $this->search_ordinance_types = $data['ordinance_types'] ?? [];       
        session(['search_ordinance_types' => $this->search_ordinance_types]);   

        $query1 = Resolutions::where("is_deleted", 0)->where("is_archived", 0);     //->selectRaw('series_number, title, author_name, date_created');
        $query2 = Ordinances::where("is_deleted", 0)->where("is_archived", 0);      //->selectRaw('ordinance_number as series_number, short_title as title, author_name, date_created');
        $query3 = Minutes::where("is_deleted", 0)->where("is_archived", 0);         //selectRaw('series_number, short_description as title, presiding_officer as author_name, date_created');
        $query4 = Communications::where("is_deleted", 0)->where("is_archived", 0);  //->selectRaw("concat(communication_type,'-',id) as series_number, particulars as title, '' as author_name, created_at as date_created");
    
        //SEARCH BY KEYWORD
        $keywords = $data['keyword']; 
        if ($keywords && $keywords!="") {
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
        $keywords = $data['title']; 
        if ($keywords && $keywords!="") {
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
        $keywords = $data['author']; 
        if ($keywords && $keywords!="") {
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
            
            $query4->whereRaw('1 = 0');
        }

        //SEARCH BY SERIES NUMBER
        $keywords = $data['series_number']; 
        if ($keywords && $keywords!="") {
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
            
            $query4->whereRaw('1 = 0');
        }

        //SEARCH BY START DATE
        $keywords = $data['start_date']; 
        if ($keywords && $keywords!="") {                                  
            $query1->whereRaw("date(date_created) >= date('".date("Y-m-d", strtotime($keywords))."')");
            $query2->whereRaw("date(date_created) >= date('".date("Y-m-d", strtotime($keywords))."')");
            $query3->whereRaw("date(date_created) >= date('".date("Y-m-d", strtotime($keywords))."')");
            $query4->whereRaw("date(created_at) >= date('".date("Y-m-d", strtotime($keywords))."')");
        }

        //SEARCH BY END DATE
        $keywords = $data['end_date']; 
        if ($keywords && $keywords!="") {                                  
            $query1->whereRaw("date(date_created) <= date('".date("Y-m-d", strtotime($keywords))."')");
            $query2->whereRaw("date(date_created) <= date('".date("Y-m-d", strtotime($keywords))."')");
            $query3->whereRaw("date(date_created) <= date('".date("Y-m-d", strtotime($keywords))."')");
            $query4->whereRaw("date(created_at) <= date('".date("Y-m-d", strtotime($keywords))."')");
        }

        //SEARCH BY ORDINANCE TYPE
        $keywords = $data['ordinance_types'] ?? []; 
        if ($keywords && $keywords!="") {              
            $query2->where(function ($q) use ($keywords) {
                foreach ($keywords as $term)                 
                    $q->orWhere('ordinance_type', $term);                                  
            });            

            $query1->whereRaw('1 = 0');
            $query3->whereRaw('1 = 0');
            $query4->whereRaw('1 = 0');
        }

    
        $results1 = array();
        $results2 = array();
        $results3 = array();
        $results4 = array();

        //$combined = collect();
        //if($data['resolutions']) $combined = $results1->merge($combined);
        //if($data['ordinances']) $combined = $results2->merge($combined);
        //if($data['minutes']) $combined = $results3->merge($combined);
        //if($data['communications']) $combined = $results4->merge($combined);

        if(isset($data['resolutions'])) $results1 = $query1->get();
        if(isset($data['ordinances'])) $results2 = $query2->get();
        if(isset($data['minutes'])) $results3 = $query3->get();
        if(isset($data['communications'])) $results4 = $query4->get();

        $data = array(
            'menu' => 'Search',
            'resolutions' => $results1,    
            'ordinances' => $results2,    
            'minutes' => $results3,    
            'communications' => $results4,                               
        );

        return view("search_results", $data);
    }

}
