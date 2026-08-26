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
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


use App\Models\SangguniangDocuments;
use App\Models\SangguniangActivities;


class SangguniangActivitiesController extends Controller
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

    public function all()
    {
        log_activity('View Sangguniang Activities - ALL');      
        $query = SangguniangActivities::where("is_deleted", 0)->where("is_archived", 0); 
        //$query = $query->where('status', 'UPCOMING');
        $records = $query->get();
        
        $data = array(
            'menu' => 'Activities - All',
            'records' => $records,        
            'mode' => 0,                                            
        );

        return view("sangguniang.list", $data);
    }

    public function upcoming()
    {
        log_activity('View Sangguniang Activities - Upcoming');      
        $query = SangguniangActivities::where("is_deleted", 0)->where("is_archived", 0); 
        $query = $query->where('status', 'UPCOMING');
        $records = $query->get();
        
        $data = array(
            'menu' => 'Activities - Upcoming',
            'records' => $records,     
            'mode' => 1,                                               
        );

        return view("sangguniang.list", $data);
    }

    public function ongoing()
    {
        log_activity('View Sangguniang Activities - Ongoing');      
        $query = SangguniangActivities::where("is_deleted", 0)->where("is_archived", 0); 
        $query = $query->where('status', 'ONGOING');
        $records = $query->get();
        
        $data = array(
            'menu' => 'Activities - Ongoing',
            'records' => $records,        
            'mode' => 2,                                            
        );

        return view("sangguniang.list", $data);
    }

    public function completed()
    {
        log_activity('View Sangguniang Activities - Completed');      
        $query = SangguniangActivities::where("is_deleted", 0)->where("is_archived", 0); 
        $query = $query->where('status', 'COMPLETED');
        $records = $query->get();
        
        $data = array(
            'menu' => 'Activities - Completed',
            'records' => $records,      
            'mode' => 3,                                              
        );

        return view("sangguniang.list", $data);
    }

    
    public function add()
    {                       
        $data = array(    
            'menu' => 'Activities - All',                  
            'info' => null,                            
            'edit' => FALSE,            
        );            
        
        return view("sangguniang.add", $data); 
    }
    

    public function edit($transid)
    {         
        $resolution_info = SangguniangActivities::where('id', $transid)->first();              
        $data = array(    
            'menu' => 'Activities - All',          
            'info' => $resolution_info,                     
            'all_documents' => SangguniangDocuments::where("activity_id", $transid)->where('is_deleted', 0)->get(),       
            'edit' => TRUE,
        );            
        
        return view("sangguniang.add", $data); 
    }


    public function save_add(Request $request)
    {
        $this->edit = FALSE;    
        $data = $request->all();
        return $this->_save_changes($data); 
    }

    public function save_changes(Request $request)
    {
        $this->edit = TRUE;    
        $data = $request->all();
        return $this->_save_changes($data); 
    }

    public function _save_changes($data)
    {
        $the_id = 0;
        if($this->edit)
           $the_id = $data["activity_id"];        
        
        
        if ( ! $this->save_activity($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' activity.'
                );              
                session()->flash('messages',$this->messages);

                return redirect()->route('activities.edit', ['id' => $the_id]);                      
        }
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Activity '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);
        
        if(isset($data['btnsaveasdraft']))
            return redirect()->route('activities.edit', ['id' => $the_id]);  
        else return redirect()->route('activities.all');                                        
    }

    public function save_activity(&$the_id, $data)
    {
        if($this->edit)
        {
            $data = [
                 'activity_title' => ($data['activity_title']),                
                 'description' => $data['description'],      
                 'duration' => $data['duration'],                    
                 'location' => $data['location'],                   
                 'event_organizers' => $data['event_organizers'],                    
                 'sponsors' => $data['sponsors'],  
                 'guests_participants' => $data['guests_participants'],  
                 'objective' => $data['objective'],  
                 'expected_attendees' => $data['expected_attendees'], 
                 'actual_attendees' => $data['actual_attendees'], 
                 'budget' => $data['budget'],  
                 'resolution' => $data['resolution'],   
                 'remarks' => $data['remarks'],     
                 'prepared_by' => $data['prepared_by'],  
                 'status' => $data['status'],  
                 'type_of_activity' => $data['type_of_activity'],  
                 'activity_date' => date("Y-m-d H:i:s", strtotime($data['activity_date'])),                                                       
            ];
            $row = SangguniangActivities::where(['id' => $the_id])->update($data);       
            log_activity('Update Sangguniang Activity', json_encode($data));                          
            return $row;
        }
        else
        {
            $data = [
                'activity_title' => ($data['activity_title']),                
                 'description' => $data['description'],      
                 'duration' => $data['duration'],                    
                 'location' => $data['location'],                   
                 'event_organizers' => $data['event_organizers'],                    
                 'sponsors' => $data['sponsors'],  
                 'guests_participants' => $data['guests_participants'],  
                 'objective' => $data['objective'],  
                 'expected_attendees' => $data['expected_attendees'], 
                 'actual_attendees' => $data['actual_attendees'], 
                 'budget' => $data['budget'],  
                 'resolution' => $data['resolution'],   
                 'remarks' => $data['remarks'],     
                 'prepared_by' => $data['prepared_by'],  
                 'status' => $data['status'],  
                 'type_of_activity' => $data['type_of_activity'],  
                 'activity_date' => date("Y-m-d H:i:s", strtotime($data['activity_date'])),                                     
                'added_by' => Auth::user()->id,
                   
            ];
            $row = SangguniangActivities::create($data);           
            log_activity('Add Sangguniang Activity', json_encode($data));   
            $the_id = $row->id;                
            return $row;
        }
        
    }

    public function delete($recordid)
    {
        SangguniangActivities::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);     
        log_activity('Delete Sangguniang Activity', json_encode(SangguniangActivities::where(['id' => $recordid])->first()->toArray()));              

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted activity.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('activities.all'); 
    }

    public function move_to_archive($recordid)
    {
        SangguniangActivities::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 1,                                 
            ]);   
        log_activity('Move to Archive - Sangguniang Activity', json_encode(SangguniangActivities::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully archived Sangguniang Activity.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('activities.all'); 
    }

    public function change_status(Request $request)
    {
        $data = $request->all();
         SangguniangActivities::where(['id' => $data['activity_id']])
            ->update([                          
                'status'=> $data['status'],                                 
            ]);   
        log_activity('Change Status - Sangguniang Activity', json_encode(SangguniangActivities::where(['id' => $data['activity_id']])->first()->toArray()));                 

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully updated status.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('sangguniang.all'); 
    }

    public function upload_supporting_documents(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('filepond')) {
            $file = $request->file('filepond');
            $filename = time() . '-' . $file->getClientOriginalName();
            //$path = $file->storeAs('uploads_resolutions', $filename, 'public');
            $file->move(public_path('uploads_sangguniang'), $filename);
            $path = public_path('uploads_sangguniang').'/'.$filename;
            $data = [                            
                'filename'=> $filename,
                'activity_id' => $request->activity_id,
            ];
            SangguniangDocuments::create($data);                 
            log_activity('Add Supporting Documents - Sangguniang Activity', json_encode($data));    

            return response()->json(['path' => $path]);           
        }       
        else
        {
            return response()->json(['msg' => 'no file uploaded']); 
        }
    }

    public function upload_supporting_documents_single(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('myfile')) {
            $file = $request->file('myfile');
            $filename = time() . '-' . $file->getClientOriginalName();
            //$path = $file->storeAs('uploads_resolutions', $filename, 'public');
            $file->move(public_path('uploads_sangguniang'), $filename);
    
            $data = [                            
                'filename'=> $filename,
                'activity_id' => $request->activity_id,
            ];
            SangguniangDocuments::create($data);       
            log_activity('Add Supporting Documents - Sangguniang Activity', json_encode($data));    

            $this->messages[] = array(
                    'type' => 'success',
                    'text' => 'Successfully added supporting document.'
            );              
            session()->flash('messages',$this->messages);
            return redirect()->route('activities.all');           
        }       
    }

    

    public function print_list($mode)
    {        
        log_activity('Export to Excel - Sangguniang Activity List');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();                
        $spreadsheet = $reader->load("templates/sangguniang.xlsx");		        
        $activeWorksheet = $spreadsheet->getSheet(0);        
        $activeWorksheet->setCellValue('A4',"Date Generated: ".date("M d, Y h:iA"));      

        $currentrow=6;             
            
        $query = SangguniangActivities::where("is_deleted", 0)->where("is_archived", 0); 
        if ($mode==1)
            $query = $query->where('status', 'UPCOMING');
        elseif ($mode==2)
            $query = $query->where('status', 'ONGOING');
        elseif ($mode==3)
            $query = $query->where('status', 'COMPLETED');
        $all_records = $query->orderBy('activity_date', 'asc')->get();   

        foreach($all_records as $item)    
        {                            
            $activeWorksheet->setCellValue("A$currentrow", $item->activity_title);                
            $activeWorksheet->setCellValue("B$currentrow", $item->description); 
            $activeWorksheet->setCellValue("C$currentrow", $item->location); 
            $activeWorksheet->setCellValue("D$currentrow", date("M d, Y h:iA", strtotime($item->activity_date))); 
            $activeWorksheet->setCellValue("E$currentrow", $item->duration);      
            $activeWorksheet->setCellValue("F$currentrow", $item->event_organizers);   
            $activeWorksheet->setCellValue("G$currentrow", $item->sponsors);        
            $activeWorksheet->setCellValue("H$currentrow", $item->guests_participants);        
            $activeWorksheet->setCellValue("I$currentrow", $item->objective);          
            $activeWorksheet->setCellValue("J$currentrow", $item->expected_attendees);     
            $activeWorksheet->setCellValue("K$currentrow", $item->actual_attendees);  
            $activeWorksheet->setCellValue("L$currentrow", $item->budget);  
            $activeWorksheet->setCellValue("M$currentrow", $item->resolution);  
            $activeWorksheet->setCellValue("N$currentrow", $item->type_of_activity);  
            $activeWorksheet->setCellValue("O$currentrow", $item->remarks);  
            $activeWorksheet->setCellValue("P$currentrow", $item->prepared_by);  
            $activeWorksheet->setCellValue("Q$currentrow", $item->status);  
            $currentrow+=1;
        }   
        $activeWorksheet->getStyle('A5'.':Q'.($currentrow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));                                

        //WRITE TO FILE        
        $activeWorksheet->getProtection()->setPassword('doctracker');
        $activeWorksheet->getProtection()->setSheet(true);
        $writer = new Xlsx($spreadsheet);
        $filename = 'SangguniangActivities'.date("Y_m_d").".xlsx";
        $writer->save("xlsx/$filename");
        
        $path = public_path("xlsx/$filename");     
        $headers = array(
            'Content-Type: xlsx',
          );
        return Response::download($path, $filename, $headers);
    }

    public function get_uploaded_files(Request $request)
    {          
        $data = $request->all();                
        $records = SangguniangDocuments::where("resolution_id", $data['resolution_id'])->where('is_deleted', 0)->get();

        $rows = array(                         
            'rows' => $records,
        );
        echo json_encode($rows);
    }

    public function delete_uploaded_file($recordid)
    {
        SangguniangDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Resolutions - Delete Uploaded File', json_encode(SangguniangDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);
        return redirect()->route('sangguniang.add'); 
       
    }

    public function delete_uploaded_file_view($recordid)
    {
        SangguniangDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Sangguniang Activities - Delete Uploaded File', json_encode(SangguniangDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);
       
        $info = SangguniangDocuments::where(['id' => $recordid])->first();
        return redirect()->route('activities.edit', ['id' => $info->activity_id]);  
    }

    public function view($transid)
    {         
        log_activity('View Sangguniang Activities', $transid);
        $key = Crypt::encrypt($transid);
        $qr_code = time().'-'.$transid;
        $qr_code = QrCode::size(200)->generate(url("/view_resolution/$key"));
        //QrCode::format('png')->size(300)->generate(url("/view_resolution/$key"), public_path("qr_codes/$qr_code"));

        $info = SangguniangActivities::where(['id' => $transid])->first();        
                  
        $data = array(    
            'menu' => 'Activities - All',            
            'info' => $info,                       
            'all_documents' => SangguniangDocuments::where("activity_id", $transid)->where('is_deleted', 0)->get(),      
            'qr_code' => $qr_code, 
            'edit' => FALSE,
        );            
        
        return view("sangguniang.view", $data); 
    }
    
}
