<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Signatories;

class SignatoriesController extends Controller
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
        $myid = Auth::user()->id;         

        $records = Signatories::get();        
                     
        $data = array(
            'menu' => 'Signatories',
            'records' => $records,                                    
        );

        return view("system.signatories", $data);
    }

    public function toggle_visibility($recordid)
    {
        $recordid = urldecode($recordid);
        $info = Signatories::where(['signatory_name' => $recordid])->first();
        Signatories::where(['signatory_name' => $recordid])
            ->update([                          
                'is_deleted'=> ($info->is_deleted==1 ? 0 : 1),                                 
            ]);              

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully toggle visibility.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('signatories.list'); 
    }

    public function save_add_signatory(Request $request) //ADD
    {
        $this->edit = FALSE;    
        $data = $request->all();
        return $this->_save_changes($data); 
    }

    public function save_changes_signatory(Request $request) //UPDATE
    {
        $this->edit = TRUE;    
        $data = $request->all();
        return $this->_save_changes($data); 
    }

    public function _save_changes($data)
    {
        $the_id = 0;
        if($this->edit)
           $the_id = $data["signatory_id"];                
        
        if ( ! $this->save_signatory($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' signatory.'
                );              
                session()->flash('messages',$this->messages);
                return redirect()->route('signatories.list');                      
        }
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Signatory '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);        
        return redirect()->route('signatories.list');                                       
    }

    public function save_signatory(&$the_id, $data)
    {
        if($this->edit)
        {
            $row = Signatories::where(['signatory_name' => $the_id])
                ->update([                                                                                                   
                    'signatory_name' => $data['signatory_name'], 
                    'position' => $data['position'],                                                                                                                       
                ]);                          
            return $row;
        }
        else
        {
            $row = Signatories::create([                                              
                'signatory_name' => $data['signatory_name'], 
                'position' => $data['position'],                                           
            ]);           
            $the_id = $row->id;                
            return $row;
        }        
    }

    public function upload_esignature(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('myfile')) {
            $file = $request->file('myfile');
            $filename = time() . '-' . $file->getClientOriginalName();
            //$path = $file->storeAs('uploads_resolutions', $filename, 'public');
            $file->move(public_path('uploads_signatures'), $filename);
      
            Signatories::where(['signatory_name' => $request->signatory_id])
                ->update([                                                                                                   
                    'esignature' => $filename,                                                                                                                                        
                ]);          

            log_activity('Upload E-Signature', json_encode(array('signatory_name' => $request->signatory_id)));    

            $this->messages[] = array(
                    'type' => 'success',
                    'text' => 'Successfully uploaded E-signature.'
            );              
            session()->flash('messages',$this->messages);
            return redirect()->route('signatories.list');           
        }       
    }
}
