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

use App\Models\Ordinances;
use App\Models\Signatories;
use App\Models\OrdinanceType;
use App\Models\OrdinancesDocuments;
use App\Models\StatusOrdinances;

class OrdinancesController extends Controller
{
    public $edit = FALSE;
    public $messages = array();
    public $selected_status = "ALL";

    public function __construct()
    {
        $this->middleware(function ($request, $next) 
        {
            if (session()->has('ordinances_selected_status')) $this->selected_status = session('ordinances_selected_status');

            if(Auth::user()->account_type!="ADMINISTRATOR" && Auth::user()->account_type!="SUPER ADMIN") return redirect('/dashboard');  
            else return $next($request);          
        });       
    }

    public function list()
    {
        log_activity('View Ordinances List');
        $myid = Auth::user()->id;         

        $query = Ordinances::where("is_deleted", 0)->where("is_archived", 0);
        if($this->selected_status!='ALL') $query = $query->where('ordinance_status', $this->selected_status);

        $records = $query->get();

        $data = array(
            'menu' => 'Ordinances',
            'all_statuses' => StatusOrdinances::where("is_deleted", 0)->orderBy('order_level')->get(),    
            'selected_status' => $this->selected_status,    
            'records' => $records,                               
        );

        return view("ordinances.list", $data);
    }

    public function list_filter(Request $request)
    {
        $data = $request->all();      
        $this->selected_status = $data['ordinance_status'];
       
        session(['ordinances_selected_status' => $this->selected_status]);        
    
        return $this->list();
    }

    public function add()
    {                       
        $data = array(    
            'menu' => 'Ordinances',  
            'all_signatories' => Signatories::where("is_deleted", 0)->get(),   
            'all_types' => OrdinanceType::where("is_deleted", 0)->get(),         
            'all_statuses' => StatusOrdinances::where("is_deleted", 0)->orderBy('order_level')->get(),      
            'info' => null,                            
            'edit' => FALSE,
            'new_ordinance_number' => $this->get_new_series(),
        );            
        
        return view("ordinances.add", $data); 
    }

    public function get_new_series()
    {       
        $records = Ordinances::whereRaw("ordinance_number like '".date("Y")."-%'")      
            ->orderBy('ordinance_number', 'desc')      
            ->limit(1)   
            ->first();
           
        if($records)
        {
            $lastId = $records->ordinance_number;
            //2025-01                
            $number = substr($lastId,5);                                        
            $newId = date("Y")."-".sprintf("%02d", $number+1);                    
            return $newId;
        }   
        else
        {
            return date("Y")."-01";
        } 
    }

    public function edit($transid)
    {         
        $ordinance_info = Ordinances::where('id', $transid)->first();              
        $data = array(    
            'menu' => 'Ordinances',            
            'info' => $ordinance_info,     
            'all_types' => OrdinanceType::where("is_deleted", 0)->get(),   
            'all_signatories' => Signatories::where("is_deleted", 0)->get(),      
            'all_statuses' => StatusOrdinances::where("is_deleted", 0)->orderBy('order_level')->get(),   
            'all_documents' => OrdinancesDocuments::where("ordinance_id", $transid)->where('is_deleted', 0)->get(),                      
            'edit' => TRUE,
        );            
        
        return view("ordinances.add", $data); 
    }

    public function view($transid)
    {         
        log_activity('View Ordinance', $transid);
        $key = Crypt::encrypt($transid);
        $qr_code = time().'-'.$transid;
        $qr_code = QrCode::size(200)->generate(url("/view_ordinance/$key"));

        $ordinance_info = Ordinances::where('id', $transid)->first();              
        $data = array(    
            'menu' => 'Ordinances',            
            'info' => $ordinance_info,    
            'all_documents' => OrdinancesDocuments::where("ordinance_id", $transid)->where('is_deleted', 0)->get(),        
            'qr_code' => $qr_code,                                 
            'edit' => FALSE,
        );            
        
        return view("ordinances.view", $data); 
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
           $the_id = $data["ordinance_id"];        
        
        
        if ( ! $this->save_ordinance($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' ordinance.'
                );              
                session()->flash('messages',$this->messages);

                return redirect()->route('ordinances.edit', ['id' => $the_id]);                      
        }
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Ordinance '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);
        
        if(isset($data['btnsaveasdraft']))
        return redirect()->route('ordinances.edit', ['id' => $the_id]);       
        else return redirect()->route('ordinances.list');            
                                         
    }

    public function save_ordinance(&$the_id, $data)
    {
        if($this->edit)
        {
            $data = [
                'ordinance_number' => ($data['ordinance_number']),                
                'author_name' => $data['author_name'],          
                'short_title' => $data['short_title'],          
                'subject_matter' => $data['subject_matter'],    
                'ordinance_type' => $data['ordinance_type'],    
                'source_book_number' => $data['source_book_number'],    
                'status' => $data['status'],    
                'sp_resolutions' => $data['sp_resolutions'],    
                'publication_postings' => date("Y-m-d", strtotime($data['publication_postings'])),                     
                'keywords_tags' => $data['keywords_tags'],                                                                   
                'editor_content' => $data['editor_content'],          
                'ordinance_status' => $data['ordinance_status'],              
                'date_created' => date("Y-m-d H:i:s", strtotime($data['date_created'])),
                'approved_date' => date("Y-m-d", strtotime($data['approved_date']))                            
            ];

            $row = Ordinances::where(['id' => $the_id])->update($data);         
            log_activity('Update Ordinance', json_encode($data));                            
            return $row;
        }
        else
        {
            $data = [
                'ordinance_number' => ($data['ordinance_number']),                
                'author_name' => $data['author_name'],         
                'short_title' => $data['short_title'],          
                'subject_matter' => $data['subject_matter'],   
                'ordinance_type' => $data['ordinance_type'],    
                'source_book_number' => $data['source_book_number'],    
                'status' => $data['status'],    
                'sp_resolutions' => $data['sp_resolutions'],    
                'publication_postings' => date("Y-m-d", strtotime($data['publication_postings'])),                  
                'keywords_tags' => $data['keywords_tags'],                                                                 
                'editor_content' => $data['editor_content'],         
                'ordinance_status' => $data['ordinance_status'],               
                'date_created' => date("Y-m-d H:i:s", strtotime($data['date_created'])),
                'approved_date' => date("Y-m-d", strtotime($data['approved_date'])) ,    
                'added_by' => Auth::user()->id,                     
            ];
            $row = Ordinances::create($data);           
            log_activity('Add Ordinance', json_encode($data)); 
            $the_id = $row->id;                
            return $row;
        }
        
    }

    public function delete($recordid)
    {
        Ordinances::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);              
        log_activity('Delete Ordinance', json_encode(Ordinances::where(['id' => $recordid])->first()->toArray()));  
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted ordinance.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('ordinances.list'); 
    }

    public function move_to_archive($recordid)
    {
        Ordinances::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 1,                                 
            ]);   
        log_activity('Move to Archive - Ordinance', json_encode(Ordinances::where(['id' => $recordid])->first()->toArray()));               

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully archived ordinance.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('ordinances.list'); 
    }

    public function change_status(Request $request)
    {
        $data = $request->all();
        Ordinances::where(['id' => $data['ordinance_id']])
            ->update([                          
                'status'=> $data['status'],                                 
            ]);   
        log_activity('Change Status - Ordinance', json_encode(Ordinances::where(['id' => $data['resolution_iod']])->first()->toArray()));                

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully updated status.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('ordinances.list'); 
    }

    public function upload_supporting_documents(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('filepond')) {
            $file = $request->file('filepond');
            $filename = time() . '-' . $file->getClientOriginalName();
            //$path = $file->storeAs('uploads_ordinances', $filename, 'public');
            $file->move(public_path('uploads_ordinances'), $filename);
            $path = public_path('uploads_ordinances').'/'.$filename;
    
            $data = [                            
                'filename'=> $filename,
                'ordinance_id' => $request->ordinance_id,
            ];
            OrdinancesDocuments::create($data);      
            log_activity('Add Supporting Documents - Ordinance', json_encode($data));   

            return response()->json(['path' => $path]);
           
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
            //$path = $file->storeAs('uploads_ordinances', $filename, 'public');
            $file->move(public_path('uploads_ordinances'), $filename);
    
            $data = [                            
                'filename'=> $filename,
                'ordinance_id' => $request->ordinance_id,
            ];
            OrdinancesDocuments::create($data);       
            log_activity('Add Supporting Documents - Ordinance', json_encode($data));   

            $this->messages[] = array(
                    'type' => 'success',
                    'text' => 'Successfully added supporting document.'
            );              
            session()->flash('messages',$this->messages);
            return redirect()->route('ordinances.list');           
        }       
    }

    public function generate_pdf($transid)
    {
        log_activity('Generate PDF - Ordinance', $transid);   
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];       
        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + [
                'nunito' => [
                    'R' => 'Nunito-Regular.ttf',
                    'B' => 'Nunito-Bold.ttf',
                    'I' => 'Nunito-Italic.ttf',
                    'BI' => 'Nunito-BoldItalic.ttf'
                ]
            ],
            'default_font' => 'nunito' // Set default font to Nunito
        ]);
        $mpdf->SetDefaultBodyCSS('line-height', '1');

        $css = file_get_contents(public_path("assets/src/plugins/css/light/editors/quill/quill.snow.css"));
        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

        $ordinance_info = Ordinances::where('id', $transid)->first();    
        $filename = "";        
        if($ordinance_info)
        {
            $html = "<div class='ql-editor'>".$ordinance_info->editor_content."</div>";
            $filename = $ordinance_info->series_number;
        }            
        else 
          $html = "<h2>File not found.</h2>";    
        
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);       

        // ADD ATTACHEMENTS
        $images = array();
        $all_attachments = OrdinancesDocuments::where("ordinance_id", $transid)->where('is_deleted', 0)->orderBy('updated_at','asc')->get();
        foreach ($all_attachments as $item)
        {
            $images[] = public_path('uploads_ordinances/'.$item->filename);
        }

        foreach ($images as $image) {
            if (file_exists($image)) {
                $mpdf->AddPage(); // create new page for each image
                
                $mpdf->WriteHTML("
                    <div style='text-align:center;'>
                        <img src='{$image}' style='width:100%; height:auto;'>
                    </div>
                ");
            }
        }

        $mpdf->Output($filename.'.pdf', 'I'); // 'I' for inline view, 'D' for download
                        
    }

    public function view_ordinance($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            echo "<h2>Invalid File URL.</h2>";
        }

        $this->generate_pdf($id);
    }

    public function print_list()
    {        
        log_activity('Export to Excel - Ordinance List');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();                
        $spreadsheet = $reader->load("templates/ordinances.xlsx");		        
        $activeWorksheet = $spreadsheet->getSheet(0);        
        $activeWorksheet->setCellValue('A4',"Date Generated: ".date("M d, Y h:iA"));      

        $currentrow=6;            

        $query = Ordinances::where("is_deleted", 0)->where("is_archived", 0);
        if($this->selected_status!='ALL') $query = $query->where('ordinance_status', $this->selected_status);

        $all_records = $query->orderBy('ordinance_number', 'asc')->get();    

        foreach($all_records as $item)    
        {                            
            $activeWorksheet->setCellValue("A$currentrow", $item->ordinance_number);       
            $activeWorksheet->setCellValue("B$currentrow", date("M d, Y", strtotime($item->approved_date)));              
            $activeWorksheet->setCellValue("C$currentrow", $item->short_title); 
            $activeWorksheet->setCellValue("D$currentrow", $item->author_name); 
            $activeWorksheet->setCellValue("E$currentrow", $item->subject_matter);      
            $activeWorksheet->setCellValue("F$currentrow", $item->source_book_number);      
            $activeWorksheet->setCellValue("G$currentrow", $item->status);      
            $activeWorksheet->setCellValue("H$currentrow", $item->sp_resolutions);    
            $activeWorksheet->setCellValue("I$currentrow", date("M d, Y", strtotime($item->publication_postings)));     
            $activeWorksheet->setCellValue("J$currentrow", $item->keywords_tags);            
            $activeWorksheet->setCellValue("K$currentrow", $item->ordinance_type);  
            $currentrow+=1;
        }   
        $activeWorksheet->getStyle('A5'.':K'.($currentrow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));                                

        //WRITE TO FILE        
        $activeWorksheet->getProtection()->setPassword('doctracker');
        $activeWorksheet->getProtection()->setSheet(true);
        $writer = new Xlsx($spreadsheet);
        $filename = 'Ordinances_'.date("Y_m_d").".xlsx";
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
        $records = OrdinancesDocuments::where("ordinance_id", $data['ordinance_id'])->where('is_deleted', 0)->get();

        $rows = array(                         
            'rows' => $records,
        );
        echo json_encode($rows);
    }

    public function delete_uploaded_file($recordid)
    {
        OrdinancesDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Ordinances - Delete Uploaded File', json_encode(OrdinancesDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);

        return redirect()->route('ordinances.list'); 
    }

    public function delete_uploaded_file_view($recordid)
    {
        OrdinancesDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Ordinances - Delete Uploaded File', json_encode(OrdinancesDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);
       
        $info = OrdinancesDocuments::where(['id' => $recordid])->first();
        return redirect()->route('ordinances.edit', ['id' => $info->ordinance_id]);  
    }
    
}
