<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\OrdinanceType;

class OrdinanceTypeController extends Controller
{
    public $edit = FALSE;
    public $messages = array();

    public function __construct()
    {
        $this->middleware(function ($request, $next) 
        {
            if(Auth::user()->account_type!="ADMINISTRATOR" && Auth::user()->account_type!="SUPER ADMIN") return redirect('/');  
            else return $next($request);          
        });       
    }

    public function list()
    {          
        $records = OrdinanceType::get();        
                     
        $data = array(
            'menu' => 'Ordinances',
            'records' => $records,                                    
        );

        return view("ordinances.ordinance_type", $data);
    }

    public function toggle_visibility($recordid)
    {
        $recordid = urldecode($recordid);
        $info = OrdinanceType::where(['ordinance_type' => $recordid])->first();
        OrdinanceType::where(['ordinance_type' => $recordid])
            ->update([                          
                'is_deleted'=> ($info->is_deleted==1 ? 0 : 1),                                   
            ]);              

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully toggle visibility.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('ordinance_type.list'); 
                         
    }

    public function save_add_type(Request $request) //ADD
    {
        $this->edit = FALSE;    
        $data = $request->all();
        return $this->_save_changes($data); 
    }

    public function save_changes_type(Request $request) //UPDATE
    {
        $this->edit = TRUE;    
        $data = $request->all();
        return $this->_save_changes($data); 
    }

    public function _save_changes($data)
    {
        $the_id = 0;
        if($this->edit)
           $the_id = $data["ordinance_type_id"];                
        
        if ( ! $this->save_type($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' ordinance type.'
                );              
                session()->flash('messages',$this->messages);
                return redirect()->route('ordinance_type.list');                      
        }
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Ordinance Type '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);        
        return redirect()->route('ordinance_type.list');                                       
    }

    public function save_type(&$the_id, $data)
    {
        if($this->edit)
        {
            $row = OrdinanceType::where(['ordinance_type' => $the_id])
                ->update([                                                                                                   
                    'ordinance_type' => $data['ordinance_type'], 
                    'description' => $data['description'],                                                                                                                       
                ]);                          
            return $row;
        }
        else
        {
            $row = OrdinanceType::create([                                              
                'ordinance_type' => $data['ordinance_type'], 
                'description' => $data['description'],                                    
            ]);           
            $the_id = $row->id;                
            return $row;
        }        
    }
}
