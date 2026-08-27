<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ActivityLogs;
use App\Models\User;
use App\Models\UserPermissions;

class SystemController extends Controller
{
    public $edit = FALSE;
    public $messages = array();
    public $selected_date_start = "";
    public $selected_date_end = "";
    public $selected_user_type = "";

    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {
            if (session()->has('logs_selected_date_start')) $this->selected_date_start = session('logs_selected_date_start');
            if (session()->has('logs_selected_date_end')) $this->selected_date_end = session('logs_selected_date_end');
            if (session()->has('selected_user_type')) $this->selected_user_type = session('selected_user_type');

            return $next($request);
        });
        $this->selected_date_start = date("Y-m-d");
        $this->selected_date_end = date("Y-m-d");

        $this->middleware('permission:View Logs')->only(['logs', 'logs_filter', 'get_log_details', 'print_logs_list']);
        $this->middleware('permission:View Users')->only(['user_list', 'get_user_info']);
        $this->middleware('permission:Add User')->only(['save_add_user']);
        $this->middleware('permission:Edit User')->only(['save_changes_user']);
        $this->middleware('permission:Reset Password')->only(['reset_password']);
        $this->middleware('permission:Archive User')->only(['move_to_archive']);
        $this->middleware('permission:Set Access Control')->only(['access_control', 'select_access_control', 'save_access_control']);
    }

    public function logs()
    {
        log_activity('View System Logs');
        $myid = Auth::user()->id;         

        $records = ActivityLogs::query()
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')   
            ->whereRaw("date(activity_date) >= '".date("Y-m-d", strtotime($this->selected_date_start))."'")
            ->whereRaw("date(activity_date) <= '".date("Y-m-d", strtotime($this->selected_date_end))."'")       
            ->selectRaw("users.*, activity_logs.*, activity_logs.id as record_id")
            ->get();        
                     
        $data = array(
            'menu' => 'User Management',
            'records' => $records,     
            'selected_date_start' => $this->selected_date_start, 
            'selected_date_end' => $this->selected_date_end,                                
        );

        return view("system.logs", $data);
    }

    public function logs_filter(Request $request)
    {
        $data = $request->all();      
        $this->selected_date_start = $data['logs_selected_date_start'];
        $this->selected_date_end = $data['logs_selected_date_end'];    
             
        session(['logs_selected_date_start' => $this->selected_date_start]);
        session(['logs_selected_date_end' => $this->selected_date_end]);        
    
        return $this->logs();
    }

    public function get_log_details(Request $request)
    {          
        $data = $request->all();                
        $records = ActivityLogs::where("id", $data['record_id'])->first();

        $rows = array(                         
            'record_info' => $records,
        );
        echo json_encode($rows);
    }

    public function user_list()
    {
        log_activity('View User List');         
        $records = User::where("is_deleted", 0)->where("is_archived", 0)->get();                             
        $data = array(
            'menu' => 'User Management',
            'records' => $records,                               
        );
        return view("system.user_list", $data);
    }   

    public function save_add_user(Request $request)
    {
        $this->edit = FALSE;    
        $data = $request->all();
        return $this->_save_changes_user($data); 
    }

    public function save_changes_user(Request $request)
    {
        $this->edit = TRUE;    
        $data = $request->all();
        return $this->_save_changes_user($data); 
    }

    public function _save_changes_user($data)
    {
        $the_id = 0;
        if($this->edit)
           $the_id = $data["user_id"];        
        
        
        if ( ! $this->save_user($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' user.'
                );              
                session()->flash('messages',$this->messages);
                return redirect()->route('system.user_list');                      
        }
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'User '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);
        
        return redirect()->route('system.user_list');                                   
    }

    public function save_user(&$the_id, $data)
    {
        if($this->edit)
        {
            $data = [                                               
                'username' => $data['username'],                                    
                'fullname' => $data['fullname'],   
                'email' => $data['email'],   
                'account_type' => $data['account_type'],
                'status' => $data['status'],                                                                          
            ];
            $row = User::where(['id' => $the_id])->update($data);      
            log_activity('Update User', json_encode($data));                    
            return $row;
        }
        else
        {
            $data = [
                'username' => $data['username'],                                    
                'fullname' => $data['fullname'],   
                'email' => $data['email'],   
                'account_type' => $data['account_type'],
                'status' => $data['status'], 
                'added_by' => Auth::user()->id,  
                'password' => bcrypt($data['password']),             
            ];
            $row = User::create($data);           
            $the_id = $row->id;        
            log_activity('Add User', json_encode($data));          
            return $row;
        }        
    }

    public function reset_password(Request $request)
    {
        $data = $request->all();
        User::where(['id' => $data['user_id']])
            ->update([                          
                'password' => bcrypt($data['password']),                         
            ]);   
        log_activity('Reset Password', json_encode(User::where(['id' => $data['user_id']])->first()->toArray()));                 

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully reset password.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('system.user_list'); 
    }

    public function move_to_archive($recordid)
    {
        User::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 1,                                 
            ]);   
        log_activity('Move to Archive - User', json_encode(User::where(['id' => $recordid])->first()->toArray()));                             
        
        return redirect()->route('system.user_list'); 
    }

    public function get_user_info(Request $request)
    {          
        $data = $request->all();                
        $records = User::where("id", $data['user_id'])->first();

        $rows = array(                         
            'info' => $records,
        );
        echo json_encode($rows);
    }

    public function access_control()
    {        
        log_activity('Access Control');                             
               
        $permissions = UserPermissions::where('account_type', $this->selected_user_type)->get();
        $permissions = $permissions->pluck('privilege')->toArray();

        $data = array(
            'menu' => 'Access Control',           
            'mgatypes' => DB::table('account_type')->get(),    
            'selected_user_type' => $this->selected_user_type,     
            'user_permissions'  => $permissions,                  
        );

        return view("system.access_control", $data);        
    }

    public function select_access_control(Request $request)
    {
        $data = $request->all();      
        $this->selected_user_type = $data['user_type'];       
        session(['selected_user_type' => $this->selected_user_type]);        
    
        return $this->access_control();
    }

    public function save_access_control(Request $request)
    {
        $data = $request->all();
        UserPermissions::where('account_type', $data['user_type'])->delete(); //delete privileges, then reinsert

        log_activity('Set Access Control of '.$data['user_type'], json_encode($data['privileges']));                 
        $chosen_permissions = $data['privileges'] ?? [];

        foreach ($chosen_permissions as $item) 
        {
            $row = [
                'account_type' => $data['user_type'],                                    
                'privilege' => $item,                          
            ];
            UserPermissions::create($row);   
        }

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully set permissions.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('system.access_control'); 
    }

    public function print_logs_list()
    {        
        log_activity('Export to Excel - Logs List');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();                
        $spreadsheet = $reader->load("templates/logs.xlsx");		        
        $activeWorksheet = $spreadsheet->getSheet(0);        
        $activeWorksheet->setCellValue('A4',"Date Generated: ".date("M d, Y h:iA"));      

        $currentrow=6;     
        $all_records = ActivityLogs::query()
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')   
            ->whereRaw("date(activity_date) >= '".date("Y-m-d", strtotime($this->selected_date_start))."'")
            ->whereRaw("date(activity_date) <= '".date("Y-m-d", strtotime($this->selected_date_end))."'")       
            ->selectRaw("users.*, activity_logs.*, activity_logs.id as record_id")
            ->get();         

        foreach($all_records as $item)    
        {                                        
            $activeWorksheet->setCellValue("A$currentrow", date("M d, Y h:iA", strtotime($item->activity_date))); 
            $activeWorksheet->setCellValue("B$currentrow", $item->fullname);        
            $activeWorksheet->setCellValue("C$currentrow", $item->username);        
            $activeWorksheet->setCellValue("D$currentrow", $item->action);          
           
            $currentrow+=1;
        }   
        $activeWorksheet->getStyle('A5'.':D'.($currentrow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));                                

        //WRITE TO FILE        
        $activeWorksheet->getProtection()->setPassword('doctracker');
        $activeWorksheet->getProtection()->setSheet(true);
        $writer = new Xlsx($spreadsheet);
        $filename = 'Logs_'.date("Y_m_d").".xlsx";
        $writer->save("xlsx/$filename");
        
        $path = public_path("xlsx/$filename");     
        $headers = array(
            'Content-Type: xlsx',
          );
        return Response::download($path, $filename, $headers);
    }
}
