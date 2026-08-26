<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Resolutions;
use App\Models\Ordinances;
use App\Models\Minutes;
use App\Models\Communications;
use App\Models\User;
use App\Models\SangguniangActivities;


class ArchiveController extends Controller
{
    public $edit = FALSE;
    public $messages = array();

    public function __construct()
    {
        $this->middleware(function ($request, $next) 
        {
            if(Auth::user()->account_type!="ADMINISTRATOR" && Auth::user()->account_type!="SUPER ADMIN") return redirect('/dashboard');  
            else return $next($request);          
        });       
    }

    public function resolutions()
    {
        log_activity('View Archived Resolutions List');
        $myid = Auth::user()->id;         

        $records = Resolutions::where("is_deleted", 0)->where("is_archived", 1)->get();        
                     
        $data = array(
            'menu' => 'Archive - Resolutions',
            'records' => $records,                               
        );

        return view("archive.resolutions", $data);
    }

    public function resolutions_unarchive($recordid)
    {
        Resolutions::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 0,                                 
            ]);   
        log_activity('Remove from Archive - Resolution', json_encode(Resolutions::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully removed resolution from archive.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('archive.resolutions'); 
    }

    public function ordinances()
    {
        log_activity('View Archived Ordinances List');
        $myid = Auth::user()->id;         

        $records = Ordinances::where("is_deleted", 0)->where("is_archived", 1)->get();        
                     
        $data = array(
            'menu' => 'Archive - Ordinances',
            'records' => $records,                               
        );

        return view("archive.ordinances", $data);
    }

    public function ordinances_unarchive($recordid)
    {
        Ordinances::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 0,                                 
            ]);   
        log_activity('Remove from Archive - Ordinance', json_encode(Ordinances::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully removed ordinance from archive.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('archive.ordinances'); 
    }

    public function minutes()
    {
        log_activity('View Archived Minutes List');
        $myid = Auth::user()->id;         

        $records = Minutes::where("is_deleted", 0)->where("is_archived", 1)->get();        
                     
        $data = array(
            'menu' => 'Archive - Minutes',
            'records' => $records,                               
        );

        return view("archive.minutes", $data);
    }

    public function minutes_unarchive($recordid)
    {
        Minutes::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 0,                                 
            ]);   
        log_activity('Remove from Archive - Minutes', json_encode(Minutes::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully removed minutes from archive.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('archive.minutes'); 
    }

    public function communications()
    {
        log_activity('View Archived Communications List');
        $myid = Auth::user()->id;         

        $records = Communications::where("is_deleted", 0)->where("is_archived", 1)->get();        
                     
        $data = array(
            'menu' => 'Archive - Communications',
            'records' => $records,                               
        );

        return view("archive.communications", $data);
    }

    public function communications_unarchive($recordid)
    {
        Communications::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 0,                                 
            ]);   
        log_activity('Remove from Archive - Communications', json_encode(Communications::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully removed communication from archive.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('archive.communications'); 
    }

    public function users()
    {
        log_activity('View Archived Users List');
        $myid = Auth::user()->id;         

        $records = User::where("is_deleted", 0)->where("is_archived", 1)->get();        
                     
        $data = array(
            'menu' => 'Archive - Users',
            'records' => $records,                               
        );

        return view("archive.users", $data);
    }

    public function users_unarchive($recordid)
    {
        User::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 0,                                 
            ]);   
        log_activity('Remove from Archive - Users', json_encode(User::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully removed user from archive.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('archive.users'); 
    }

    public function sangguniang()
    {
        log_activity('View Archived Sangguniang Activities List');
        $myid = Auth::user()->id;         

        $records = SangguniangActivities::where("is_deleted", 0)->where("is_archived", 1)->get();        
                     
        $data = array(
            'menu' => 'Archive - Sangguniang',
            'records' => $records,                               
        );

        return view("archive.sangguniang", $data);
    }

    public function sangguniang_unarchive($recordid)
    {
        SangguniangActivities::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 0,                                 
            ]);   
        log_activity('Remove from Archive - Sangguniang Activities', json_encode(SangguniangActivities::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully removed sangguniang activity from archive.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('archive.sangguniang'); 
    }
}
