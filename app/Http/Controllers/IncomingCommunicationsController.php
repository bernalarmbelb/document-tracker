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

use App\Models\Communications;
use App\Models\CommunicationsDocuments;

class IncomingCommunicationsController extends Controller
{
    public $edit = FALSE;
    public $messages = array();

    public function __construct()
    {
        $this->middleware('permission:View Communications')->only(['incoming_list', 'print_incoming', 'get_incoming_info', 'get_incoming_files', 'view']);
        $this->middleware('permission:Add Communication')->only(['save_add_incoming', 'upload_supporting_documents_incoming']);
        $this->middleware('permission:Edit Communication')->only(['save_changes_incoming']);
        $this->middleware('permission:Archive Communication')->only(['move_to_archive_incoming']);
        $this->middleware('permission:Add Communication,Edit Communication')->only(['delete_uploaded_file_incoming']);
    }

    public function incoming_list()
    {
        log_activity('View Communications - Incoming List');
        $myid = Auth::user()->id;         
        $records = Communications::where("communication_type", 'INCOMING')->where("is_deleted", 0)->where("is_archived", 0)->get();                             
        $data = array(
            'menu' => 'Communications',
            'records' => $records,                               
        );
        return view("communications.list_incoming", $data);
    }   
    

    public function save_add_incoming(Request $request)
    {
        $this->edit = FALSE;    
        $data = $request->all();
        return $this->_save_changes_incoming($data); 
    }

    public function save_changes_incoming(Request $request)
    {
        $this->edit = TRUE;    
        $data = $request->all();
        return $this->_save_changes_incoming($data); 
    }

    public function _save_changes_incoming($data)
    {
        $the_id = 0;
        if($this->edit)
           $the_id = $data["communication_id"];        
        
        
        if ( ! $this->save_incoming($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' incoming communication.'
                );              
                session()->flash('messages',$this->messages);
                return redirect()->route('communications.incoming_list');                      
        }
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Incoming Communication '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);
        
        return redirect()->route('communications.incoming_list');                                       
    }

    public function save_incoming(&$the_id, $data)
    {
        if($this->edit)
        {
            $data = [                               
                'date_received' => date("Y-m-d", strtotime($data['date_received'])),        
                'source' => $data['source'],                                    
                'particulars' => $data['particulars'],   
                'actions_taken' => $data['actions_taken'],   
                'received_by' => $data['received_by'],                                                                
            ];
            $row = Communications::where(['id' => $the_id])->update($data);      
            log_activity('Update Communications (Incoming)', json_encode($data));                    
            return $row;
        }
        else
        {
            $data = [
                'communication_type' => 'INCOMING',                
                'date_received' => date("Y-m-d", strtotime($data['date_received'])),   
                'source' => $data['source'],                                    
                'particulars' => $data['particulars'],   
                'actions_taken' => $data['actions_taken'],   
                'received_by' => $data['received_by'],      
                'added_by' => Auth::user()->id,               
            ];
            $row = Communications::create($data);           
            $the_id = $row->id;        
            log_activity('Add Communications (Incoming)', json_encode($data));          
            return $row;
        }        
    }

    public function move_to_archive_incoming($recordid)
    {
        Communications::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 1,                                 
            ]);      

        log_activity('Move to Archive Communications (Incoming)', json_encode(Communications::where(['id' => $recordid])->first()->toArray()));  
        
        
        return redirect()->route('communications.incoming_list'); 
    }

    public function print_incoming()
    {        
        log_activity('Export to Excel - Incoming Communications List');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();                
        $spreadsheet = $reader->load("templates/incoming.xlsx");		        

        $activeWorksheet = $spreadsheet->getSheet(0);        
        $activeWorksheet->setCellValue('A4',"Date Generated: ".date("M d, Y h:iA"));      

        $currentrow=6;     
        $all_records = Communications::where("communication_type", 'INCOMING')
            ->where("is_deleted", 0)->where("is_archived", 0)
            ->orderBy('date_received', 'desc')
            ->get();    

        foreach($all_records as $item)    
        {                            
            $activeWorksheet->setCellValue("A$currentrow", date("Y-m-d", strtotime($item->date_received)));           
            $activeWorksheet->setCellValue("B$currentrow", $item->source); 
            $activeWorksheet->setCellValue("C$currentrow", $item->particulars); 
            $activeWorksheet->setCellValue("D$currentrow", $item->actions_taken);      
            $activeWorksheet->setCellValue("E$currentrow", $item->received_by);              
            $currentrow+=1;
        }   
        $activeWorksheet->getStyle('A5'.':E'.($currentrow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));                                

        //WRITE TO FILE        
        $activeWorksheet->getProtection()->setPassword('doctracker');
        $activeWorksheet->getProtection()->setSheet(true);
        $writer = new Xlsx($spreadsheet);
        $filename = 'Incoming_'.date("Y_m_d").".xlsx";
        $writer->save("xlsx/$filename");
        
        $path = public_path("xlsx/$filename");     
        $headers = array(
            'Content-Type: xlsx',
          );
        return Response::download($path, $filename, $headers);
    }

    public function get_incoming_info(Request $request)
    {          
        $data = $request->all();                
        $records = Communications::where("id", $data['communication_id'])->first();

        $rows = array(                         
            'info' => $records,
        );
        echo json_encode($rows);
    }

    public function upload_supporting_documents_incoming(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('myfile')) {
            $file = $request->file('myfile');
            $filename = time() . '-' . $file->getClientOriginalName();
            //$path = $file->storeAs('uploads_communications', $filename, 'public');
            $file->move(public_path('uploads_communications'), $filename);
    
            $data = [                            
                'filename'=> $filename,
                'communication_id' => $request->communication_id,
            ];
            CommunicationsDocuments::create($data);
            log_activity('Add Supporting Documents - Communications', json_encode($data));

            return response($filename, 200);
        }

        return response('No file uploaded.', 422);
    }

    public function get_incoming_files(Request $request)
    {          
        $data = $request->all();                
        $records = CommunicationsDocuments::where("communication_id", $data['communication_id'])->where('is_deleted', 0)->get();

        $rows = array(                         
            'rows' => $records,
        );
        echo json_encode($rows);
    }

    public function view($transid)
    {                 
        $communication_info = Communications::where('id', $transid)->first();              
        $data = array(    
            'menu' => 'Communications',            
            'info' => $communication_info,                       
            'all_documents' => CommunicationsDocuments::where("communication_id", $transid)->where('is_deleted', 0)->get(),
            'edit' => FALSE,
        );            
        
        return view("communications.view", $data); 
    }

    public function delete_uploaded_file_incoming($recordid)
    {
        CommunicationsDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Incoming Communications - Delete Uploaded File (Incoming)', json_encode(CommunicationsDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);

        return redirect()->route('communications.incoming_list'); 
    }

    

}
