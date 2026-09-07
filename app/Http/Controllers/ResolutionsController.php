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

use App\Models\Resolutions;
use App\Models\ResolutionsDocuments;
use App\Models\Signatories;
use App\Models\StatusResolutions;


class ResolutionsController extends Controller
{
    public $edit = FALSE;
    public $messages = array();
    public $selected_status = "ALL";
    private $pendingSignatureTempFiles = [];


    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {
            if (session()->has('resolutions_selected_status')) $this->selected_status = session('resolutions_selected_status');

            return $next($request);
        });

        $this->middleware('permission:View Resolutions')->only(['list', 'list_filter', 'view', 'view_resolution', 'generate_pdf', 'print_list', 'get_uploaded_files']);
        $this->middleware('permission:Add Resolution')->only(['add', 'save_add', 'upload_supporting_documents', 'upload_supporting_documents_single']);
        $this->middleware('permission:Edit Resolution')->only(['edit', 'save_changes']);
        $this->middleware('permission:Archive Resolution')->only(['delete', 'move_to_archive']);
        $this->middleware('permission:Add Resolution,Edit Resolution')->only(['delete_uploaded_file', 'delete_uploaded_file_view']);
    }

    public function list()
    {
        log_activity('View Resolutions List');
        $myid = Auth::user()->id;         

        $query = Resolutions::where("is_deleted", 0)->where("is_archived", 0); 
        if($this->selected_status!='ALL') $query = $query->where('resolution_status', $this->selected_status);

        $records = $query->get();
        
        $data = array(
            'menu' => 'Resolutions',
            'records' => $records,       
            'all_statuses' => StatusResolutions::where("is_deleted", 0)->orderBy('order_level')->get(),    
            'selected_status' => $this->selected_status,                         
        );

        return view("resolutions.list", $data);
    }

    public function list_filter(Request $request)
    {
        $data = $request->all();      
        $this->selected_status = $data['resolution_status'];
       
        session(['resolutions_selected_status' => $this->selected_status]);        
    
        return $this->list();
    }

    public function add()
    {                       
        $data = array(    
            'menu' => 'Resolutions',  
            'all_signatories' => Signatories::where("is_deleted", 0)->get(),   
            'all_statuses' => StatusResolutions::where("is_deleted", 0)->orderBy('order_level')->get(),            
            'info' => null,                            
            'edit' => FALSE,
            'new_resolution_number' => $this->get_new_series(),
        );            
        
        return view("resolutions.add", $data); 
    }

    public function get_new_series()
    {
        $prefix = "05";
        $records = Resolutions::whereRaw("series_number like '%-".date("Y")."'")      
            ->orderBy('series_number', 'desc')      
            ->limit(1)   
            ->first();
           
        if($records)
        {
            $lastId = $records->series_number;
            //01-2025                
            $number = substr($lastId,0,2);                                        
            $newId = sprintf("%02d", $number+1)."-".date("Y");                    
            return $newId;
        }   
        else
        {
            return "01-".date("Y");
        } 
    }

    public function edit($transid)
    {         
        $resolution_info = Resolutions::where('id', $transid)->first();              
        $data = array(    
            'menu' => 'Resolutions',            
            'info' => $resolution_info,     
            'all_statuses' => StatusResolutions::where("is_deleted", 0)->orderBy('order_level')->get(),   
            'all_signatories' => Signatories::where("is_deleted", 0)->get(),             
            'all_documents' => ResolutionsDocuments::where("resolution_id", $transid)->where('is_deleted', 0)->get(),       
            'edit' => TRUE,
        );            
        
        return view("resolutions.add", $data); 
    }

    public function view($transid)
    {         
        log_activity('View Resolutions', $transid);
        $key = Crypt::encrypt($transid);
        $qr_code = time().'-'.$transid;
        $qr_code = QrCode::size(200)->generate(url("/view_resolution/$key"));
        //QrCode::format('png')->size(300)->generate(url("/view_resolution/$key"), public_path("qr_codes/$qr_code"));

        $resolution_info = Resolutions::where('id', $transid)->first();
        if ($resolution_info)  $resolution_info->editor_content = $this->replace_short_codes($resolution_info, $resolution_info->editor_content); 
                  
        $data = array(    
            'menu' => 'Resolutions',            
            'info' => $resolution_info,                       
            'all_documents' => ResolutionsDocuments::where("resolution_id", $transid)->where('is_deleted', 0)->get(),      
            'qr_code' => $qr_code, 
            'edit' => FALSE,
        );            
        
        return view("resolutions.view", $data); 
    }

    private function replace_short_codes($info, $editor_content)
    {
        // 
        $attested = Signatories::where("signatory_name", $info->attested_by)->first();
        $recorded = Signatories::where("signatory_name", $info->recorded_by)->first();
        $approved = Signatories::where("signatory_name", $info->approved_by)->first();

        if($attested && $attested->esignature!='')
        {
            $imgurl = "<img src='".upload_url('uploads_signatures', $attested->esignature)."' style='height: 50px' /> <br/>";
            $editor_content = str_replace("[attested_by]", $imgurl.$info->attested_by, $editor_content);
        }
        else
            $editor_content = str_replace("[attested_by]", $info->attested_by, $editor_content);

        if($recorded && $recorded->esignature!='')
        {
            $imgurl = "<img src='".upload_url('uploads_signatures', $recorded->esignature)."' style='height: 50px' /> <br/>";
            $editor_content = str_replace("[recorded_by]", $imgurl.$info->recorded_by, $editor_content);
        }
        else
            $editor_content = str_replace("[recorded_by]", $info->recorded_by, $editor_content);

        // if($approved->esignature!='')
        // {
        //     $imgurl = "<img src='".upload_url('uploads_signatures', $approved->esignature)."' style='height: 50px' /> <br/>";
        //     $editor_content = str_replace("[approved_by]", $imgurl.$info->approved_by, $editor_content);
        // }

        if ($approved && !empty($approved->esignature))
        {
            $imgurl = "<img src='".upload_url('uploads_signatures', $approved->esignature)."' style='height: 50px' /> <br/>";
            $editor_content = str_replace("[approved_by]", $imgurl.$info->approved_by, $editor_content);
        }


        else 
            $editor_content = str_replace("[approved_by]", $info->approved_by, $editor_content);

        return $editor_content;
    }

    private function replace_short_codes_absolute($info, $editor_content)
    {
        // 
        $attested = Signatories::where("signatory_name", $info->attested_by)->first();
        $recorded = Signatories::where("signatory_name", $info->recorded_by)->first();
        $approved = Signatories::where("signatory_name", $info->approved_by)->first();

        $signatureTempFiles = [];

        if($attested && $attested->esignature!='')
        {
            $tmp = upload_local_copy('uploads_signatures', $attested->esignature);
            $signatureTempFiles[] = $tmp;
            $imgurl = "<img src='".$tmp."' style='height: 50px' /> <br/>";
            $editor_content = str_replace("[attested_by]", $imgurl.$info->attested_by, $editor_content);
        }
        else
            $editor_content = str_replace("[attested_by]", $info->attested_by, $editor_content);

        if($recorded && $recorded->esignature!='')
        {
            $tmp = upload_local_copy('uploads_signatures', $recorded->esignature);
            $signatureTempFiles[] = $tmp;
            $imgurl = "<img src='".$tmp."' style='height: 50px' /> <br/>";
            $editor_content = str_replace("[recorded_by]", $imgurl.$info->recorded_by, $editor_content);
        }
        else
            $editor_content = str_replace("[recorded_by]", $info->recorded_by, $editor_content);

        // if($approved->esignature!='')
        if ($approved && !empty($approved->esignature))
        {
            $tmp = upload_local_copy('uploads_signatures', $approved->esignature);
            $signatureTempFiles[] = $tmp;
            $imgurl = "<img src='".$tmp."' style='height: 50px' /> <br/>";
            $editor_content = str_replace("[approved_by]", $imgurl.$info->approved_by, $editor_content);
        }
        else
            $editor_content = str_replace("[approved_by]", $info->approved_by, $editor_content);

        $this->pendingSignatureTempFiles = array_filter($signatureTempFiles);

        return $editor_content;
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
           $the_id = $data["resolution_id"];        
        
        
        if ( ! $this->save_resolution($the_id, $data))
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' resolution.'
                );
                session()->flash('messages',$this->messages);

                if (isset($data['ajax'])) return response()->json(['error' => 'Error '.($this->edit?'updating':'adding').' resolution.'], 422);
                return redirect()->route('resolutions.edit', ['id' => $the_id]);
        }

        if (isset($data['ajax'])) return response()->json(['id' => $the_id]);

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Resolution '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);
        
        if(isset($data['btnsaveasdraft']))
            return redirect()->route('resolutions.edit', ['id' => $the_id]);  
        else return redirect()->route('resolutions.list');                                        
    }

    public function save_resolution(&$the_id, $data)
    {
        if($this->edit)
        {
            $data = [
                'series_number' => ($data['series_number']),                
                 'title' => $data['title'],      
                 'author_name' => $data['author_name'],                    
                 'keywords_tags' => $data['keywords_tags'],                   
                 'sponsor' => $data['sponsor'],                    
                 'attested_by' => $data['attested_by'],  
                 'recorded_by' => $data['recorded_by'],  
                 'approved_by' => $data['approved_by'],  
                 'book_ref_no' => $data['book_ref_no'], 
                 'page_ref_no' => $data['page_ref_no'], 
                 'total_pages' => $data['total_pages'],  
                 'minute_ref_no' => $data['minute_ref_no'],   
                 'editor_content' => $data['editor_content'],     
                 'resolution_status' => $data['resolution_status'],  
                 'approved_date' => date("Y-m-d", strtotime($data['approved_date'])),                     
                 'date_created' => date("Y-m-d H:i:s", strtotime($data['date_created'])) ,                         
            ];
            $row = Resolutions::where(['id' => $the_id])->update($data);       
            log_activity('Update Resolution', json_encode($data));                          
            return $row;
        }
        else
        {
            $data = [
                'series_number' => ($data['series_number']),                
                'title' => $data['title'],            
                'author_name' => $data['author_name'],          
                'keywords_tags' => $data['keywords_tags'],                   
                'sponsor' => $data['sponsor'],               
                'attested_by' => $data['attested_by'],  
                'recorded_by' => $data['recorded_by'],  
                'approved_by' => $data['approved_by'],  
                'book_ref_no' => $data['book_ref_no'], 
                'page_ref_no' => $data['page_ref_no'], 
                'total_pages' => $data['total_pages'],  
                'minute_ref_no' => $data['minute_ref_no'], 
                'editor_content' => $data['editor_content'],    
                'resolution_status' => $data['resolution_status'], 
                'approved_date' => date("Y-m-d", strtotime($data['approved_date'])),   
                'added_by' => Auth::user()->id,
                'date_created' => date("Y-m-d H:i:s", strtotime($data['date_created'])),        
            ];
            $row = Resolutions::create($data);           
            log_activity('Add Resolution', json_encode($data));   
            $the_id = $row->id;                
            return $row;
        }
        
    }

    public function delete($recordid)
    {
        Resolutions::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);     
        log_activity('Delete Resolution', json_encode(Resolutions::where(['id' => $recordid])->first()->toArray()));              

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted resolution.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('resolutions.list'); 
    }

    public function move_to_archive($recordid)
    {
        Resolutions::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 1,                                 
            ]);   
        log_activity('Move to Archive - Resolution', json_encode(Resolutions::where(['id' => $recordid])->first()->toArray()));                     

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully archived resolution.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('resolutions.list'); 
    }

    public function change_status(Request $request)
    {
        $data = $request->all();
         Resolutions::where(['id' => $data['resolution_iod']])
            ->update([                          
                'status'=> $data['status'],                                 
            ]);   
        log_activity('Change Status - Resolution', json_encode(Resolutions::where(['id' => $data['resolution_iod']])->first()->toArray()));                 

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully updated status.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('resolutions.list'); 
    }

    public function upload_supporting_documents(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('filepond')) {
            $file = $request->file('filepond');
            $filename = time() . '-' . $file->getClientOriginalName();
            upload_disk()->putFileAs('uploads_resolutions', $file, $filename);
            $path = upload_url('uploads_resolutions', $filename);
            $data = [                            
                'filename'=> $filename,
                'resolution_id' => $request->resolution_id,
            ];
            ResolutionsDocuments::create($data);                 
            log_activity('Add Supporting Documents - Resolution', json_encode($data));    

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
            upload_disk()->putFileAs('uploads_resolutions', $file, $filename);
    
            $data = [                            
                'filename'=> $filename,
                'resolution_id' => $request->resolution_id,
            ];
            ResolutionsDocuments::create($data);       
            log_activity('Add Supporting Documents - Resolution', json_encode($data));    

            $this->messages[] = array(
                    'type' => 'success',
                    'text' => 'Successfully added supporting document.'
            );              
            session()->flash('messages',$this->messages);
            return redirect()->route('resolutions.list');           
        }       
    }

    public function generate_pdf($transid)
    {
        log_activity('Generate PDF - Resolution', $transid);   
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
        $css .= '.ql-editor p { margin: 0; }'; // tight paragraph spacing to match on-screen view
        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

        $resolution_info = Resolutions::where('id', $transid)->first();  
        if ($resolution_info)  $resolution_info->editor_content = $this->replace_short_codes_absolute($resolution_info, $resolution_info->editor_content); 
        
        $filename = "";        
        if($resolution_info)
        {            
            $html = "<div class='ql-editor'>".$resolution_info->editor_content."</div>";
            $filename = $resolution_info->series_number;
        }            
        else 
          $html = "<h2>File not found.</h2>";    
        
        $mpdf->WriteHTML($html);

        // esignature images have now been embedded into the PDF; the local temp copies can go
        foreach ($this->pendingSignatureTempFiles as $tmp) {
            @unlink($tmp);
        }
        $this->pendingSignatureTempFiles = [];

        // ADD ATTACHEMENTS
        $images = array();
        $all_attachments = ResolutionsDocuments::where("resolution_id", $transid)->where('is_deleted', 0)->orderBy('updated_at','asc')->get();
        foreach ($all_attachments as $item)
        {
            $images[] = upload_local_copy('uploads_resolutions', $item->filename);
        }

        foreach ($images as $image) {
            if ($image && file_exists($image)) {
                $mpdf->AddPage(); // create new page for each image

                $mpdf->WriteHTML("
                    <div style='text-align:center;'>
                        <img src='{$image}' style='width:100%; height:auto;'>
                    </div>
                ");

                @unlink($image);
            }
        }

        $mpdf->Output($filename.'.pdf', 'I'); // 'I' for inline view, 'D' for download
    }

    public function view_resolution($id)
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
        log_activity('Export to Excel - Resolution List');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();                
        $spreadsheet = $reader->load("templates/resolutions.xlsx");		        
        $activeWorksheet = $spreadsheet->getSheet(0);        
        $activeWorksheet->setCellValue('A4',"Date Generated: ".date("M d, Y h:iA"));      

        $currentrow=6;             
            
        $query = Resolutions::where("is_deleted", 0)->where("is_archived", 0); 
        if($this->selected_status!='ALL') $query = $query->where('resolution_status', $this->selected_status);
        $all_records = $query->orderBy('series_number', 'asc')->get();   

        foreach($all_records as $item)    
        {                            
            $activeWorksheet->setCellValue("A$currentrow", $item->series_number);                
            $activeWorksheet->setCellValue("B$currentrow", $item->title); 
            $activeWorksheet->setCellValue("C$currentrow", $item->author_name); 
            $activeWorksheet->setCellValue("D$currentrow", $item->keywords_tags); 
            $activeWorksheet->setCellValue("E$currentrow", $item->sponsor);      
            $activeWorksheet->setCellValue("F$currentrow", date("M d, Y h:iA", strtotime($item->date_created))); 
            $activeWorksheet->setCellValue("G$currentrow", $item->attested_by);        
            $activeWorksheet->setCellValue("H$currentrow", $item->recorded_by);        
            $activeWorksheet->setCellValue("I$currentrow", $item->approved_by);          
            $activeWorksheet->setCellValue("J$currentrow", date("M d, Y", strtotime($item->approved_date)));     
            $activeWorksheet->setCellValue("K$currentrow", $item->book_ref_no);  
            $activeWorksheet->setCellValue("L$currentrow", $item->page_ref_no);  
            $activeWorksheet->setCellValue("M$currentrow", $item->total_pages);  
            $activeWorksheet->setCellValue("N$currentrow", $item->minute_ref_no);  
            $currentrow+=1;
        }   
        $activeWorksheet->getStyle('A5'.':N'.($currentrow-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));                                

        //WRITE TO FILE        
        $activeWorksheet->getProtection()->setPassword('doctracker');
        $activeWorksheet->getProtection()->setSheet(true);
        $writer = new Xlsx($spreadsheet);
        $filename = 'Resolutions_'.date("Y_m_d").".xlsx";
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
        $records = ResolutionsDocuments::where("resolution_id", $data['resolution_id'])->where('is_deleted', 0)->get();
        $records->each(fn ($r) => $r->url = upload_url('uploads_resolutions', $r->filename));

        $rows = array(
            'rows' => $records,
        );
        echo json_encode($rows);
    }

    public function delete_uploaded_file($recordid)
    {
        ResolutionsDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Resolutions - Delete Uploaded File', json_encode(ResolutionsDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);
        return redirect()->route('resolutions.list'); 
       
    }

    public function delete_uploaded_file_view($recordid)
    {
        ResolutionsDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Resolutions - Delete Uploaded File', json_encode(ResolutionsDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);
       
        $info = ResolutionsDocuments::where(['id' => $recordid])->first();
        return redirect()->route('resolutions.edit', ['id' => $info->resolution_id]);  
    }
    
}
