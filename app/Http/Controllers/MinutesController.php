<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use App\Models\Minutes;
use App\Models\MinutesDocuments;
use App\Models\Signatories;
use App\Models\Member;
use App\Models\MinutesAttendance;
use App\Support\AttendanceReport;

class MinutesController extends Controller
{
    public $edit = FALSE;
    public $messages = array();
    public $keyword = "";

    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {
            if (session()->has('minutes_keyword')) $this->keyword = session('minutes_keyword');

            return $next($request);
        });

        $this->middleware('permission:View Minutes')->only(['list', 'list_grid', 'attendance_report', 'view', 'view_minute', 'generate_pdf', 'search', 'search_grid']);
        $this->middleware('permission:Add Minute')->only(['add', 'save_add', 'upload_supporting_documents']);
        $this->middleware('permission:Edit Minute')->only(['edit', 'save_changes']);
        $this->middleware('permission:Archive Minute')->only(['delete', 'move_to_archive']);
        $this->middleware('permission:Add Minute,Edit Minute')->only(['delete_uploaded_file_view']);
    }

    public function list()
    {
        log_activity('View Minutes List');
        $myid = Auth::user()->id;         
        $keyword = $this->keyword;
        $records = Minutes::where("is_deleted", 0)->where("is_archived", 0)->orderBy('date_created', 'desc')
            ->whereRaw("(series_number like '%$keyword%' or short_description like '%$keyword%' or presiding_officer like '%$keyword%' or attendance like '%$keyword%' or agenda_1 like '%$keyword%' or agenda_2 like '%$keyword%' or agenda_3 like '%$keyword%' or agenda_4 like '%$keyword%' or agenda_5 like '%$keyword%')")->get();                
                     
        $data = array(
            'menu' => 'Minutes',
            'records' => $records,
          
            'keyword' => $keyword,                             
        );

        return view("minutes.list", $data);
    }

    public function list_grid()
    {
        log_activity('View Minutes List');
        $myid = Auth::user()->id;         
        $keyword = $this->keyword;
        $regulars = Minutes::where('category','Regular Session')->where("is_deleted", 0)->where("is_archived", 0)->orderBy('date_created', 'desc')
            ->whereRaw("(series_number like '%$keyword%' or short_description like '%$keyword%' or presiding_officer like '%$keyword%' or attendance like '%$keyword%' or agenda_1 like '%$keyword%' or agenda_2 like '%$keyword%' or agenda_3 like '%$keyword%' or agenda_4 like '%$keyword%' or agenda_5 like '%$keyword%')")->get();  
        $specials = Minutes::where('category','Special Session')->where("is_deleted", 0)->where("is_archived", 0)->orderBy('date_created', 'desc')
            ->whereRaw("(series_number like '%$keyword%' or short_description like '%$keyword%' or presiding_officer like '%$keyword%' or attendance like '%$keyword%' or agenda_1 like '%$keyword%' or agenda_2 like '%$keyword%' or agenda_3 like '%$keyword%' or agenda_4 like '%$keyword%' or agenda_5 like '%$keyword%')")->get();    
        $hearings = Minutes::where('category','Committee Hearing')->where("is_deleted", 0)->where("is_archived", 0)->orderBy('date_created', 'desc')
            ->whereRaw("(series_number like '%$keyword%' or short_description like '%$keyword%' or presiding_officer like '%$keyword%' or attendance like '%$keyword%' or agenda_1 like '%$keyword%' or agenda_2 like '%$keyword%' or agenda_3 like '%$keyword%' or agenda_4 like '%$keyword%' or agenda_5 like '%$keyword%')")->get();         
                     
        $data = array(
            'menu' => 'Minutes',
            'all_regulars' => $regulars,
            'all_specials' => $specials,
            'all_hearings' => $hearings,  
            'keyword' => $keyword,                             
        );

        return view("minutes.list_grid", $data);
    }

    public function search(Request $request)
    {
        $data = $request->all();      
        $this->keyword = $data['keyword'];       
        session(['minutes_keyword' => $this->keyword]);        
    
        return $this->list();
    }

    public function search_grid(Request $request)
    {
        $data = $request->all();      
        $this->keyword = $data['keyword'];       
        session(['minutes_keyword' => $this->keyword]);        
    
        return $this->list_grid();
    }

    public function add($category)
    {                       
        $categorylist = array("","Regular Session","Committee Hearing","Special Session");
        $data = array(    
            'menu' => 'Minutes',  
            'all_signatories' => Signatories::where("is_deleted", 0)->get(),       
            'category' => $categorylist[$category],     
            'info' => null,
            'edit' => FALSE,
            'new_series_number' => $this->get_new_series(),
            'all_members' => Member::where('is_deleted', 0)->orderBy('name')->get(),
            'attendance_map' => [],
        );
        
        return view("minutes.add", $data); 
    }

    public function get_new_series()
    {       
        $records = Minutes::whereRaw("series_number like '".date("Y")."-%'")      
            ->orderBy('series_number', 'desc')      
            ->limit(1)   
            ->first();
           
        if($records)
        {
            $lastId = $records->series_number;
            //2025-01                
            $number = substr($lastId,5);                                        
            $newId = date("Y")."-".sprintf("%03d", $number+1);                    
            return $newId;
        }   
        else
        {
            return date("Y")."-001";
        } 
    }

    public function edit($transid)
    {
        $minute_info = Minutes::where('id', $transid)->first();
        $attendance_map = MinutesAttendance::where('minute_id', $transid)
            ->pluck('status', 'member_id')->toArray();
        $data = array(
            'menu' => 'Minutes',
            'info' => $minute_info,
            'all_signatories' => Signatories::where("is_deleted", 0)->get(),
            'all_documents' => MinutesDocuments::where("minute_id", $transid)->where('is_deleted', 0)->get(),
            'edit' => TRUE,
            'all_members' => Member::where('is_deleted', 0)->orderBy('name')->get(),
            'attendance_map' => $attendance_map,
        );

        return view("minutes.add", $data);
    }

    public function view($transid)
    {         
        $key = Crypt::encrypt($transid);
        $qr_code = time().'-'.$transid;
        $qr_code = QrCode::size(200)->generate(url("/view_minute/$key"));

        $minute_info = Minutes::where('id', $transid)->first();              
        $data = array(    
            'menu' => 'Minutes',            
            'info' => $minute_info,                       
            'all_documents' => MinutesDocuments::where("minute_id", $transid)->where('is_deleted', 0)->get(),       
            'qr_code' => $qr_code, 
            'edit' => FALSE,
        );            
        
        return view("minutes.view", $data); 
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
           $the_id = $data["minute_id"];        
        
        
        if ( $this->save_minute($the_id, $data) === false )
        {
                $this->messages[] = array(
                        'type' => 'danger',
                        'text' => 'Error '.($this->edit?'updating':'adding').' minute.'
                );
                session()->flash('messages',$this->messages);

                if (isset($data['ajax'])) return response()->json(['error' => 'Error '.($this->edit?'updating':'adding').' minute.'], 422);
                return redirect()->route('minutes.edit', ['id' => $the_id]);
        }

        $this->save_attendance($the_id, $data);

        if (isset($data['ajax'])) return response()->json(['id' => $the_id]);

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Minute '.($this->edit ? 'updated':'added').' successfully.'
        );	                                                       
        session()->flash('messages',$this->messages);
        
        if(isset($data['btnsaveasdraft']))
        return redirect()->route('minutes.edit', ['id' => $the_id]);              
        else return redirect()->route('minutes.list');                                          
    }

    public function save_minute(&$the_id, $data)
    {
        if($this->edit)
        {
            $data = [
                'series_number' => ($data['series_number']),                
                 'short_description' => $data['short_description'],                    
                 'category' => $data['category'],                   
                 'presiding_officer' => $data['presiding_officer'],                    
                 'attendance' => $data['attendance'],  
                 'venue' => $data['venue'],
                 'agenda_1' => $data['agenda_1'], 
                 'agenda_2' => $data['agenda_2'], 
                 'agenda_3' => $data['agenda_3'], 
                 'agenda_4' => $data['agenda_4'], 
                 'agenda_5' => $data['agenda_5'],                    
                 'editor_content' => $data['editor_content'],                      
                 'date_created' => date("Y-m-d H:i:s", strtotime($data['date_created']))                          
            ];
            $row = Minutes::where(['id' => $the_id])->update($data);     
                
            log_activity('Update Minutes', json_encode($data));      
            return $row;
        }
        else
        {
            $data = [
                'series_number' => $data['series_number'] ?? '',
                'short_description' => $data['short_description'] ?? '',
                'category' => $data['category'] ?? '',
                'presiding_officer' => $data['presiding_officer'] ?? '',
                'attendance' => $data['attendance'] ?? '',
                'venue' => $data['venue'] ?? '',
                'agenda_1' => $data['agenda_1'] ?? '',
                'agenda_2' => $data['agenda_2'] ?? '',
                'agenda_3' => $data['agenda_3'] ?? '',
                'agenda_4' => $data['agenda_4'] ?? '',
                'agenda_5' => $data['agenda_5'] ?? '',
                'editor_content' => $data['editor_content'] ?? '',
                'date_created' => date("Y-m-d H:i:s", strtotime($data['date_created'])),
                'added_by' => Auth::user()->id,
            ];
            $row = Minutes::create($data);
            $the_id = $row->id;   
            log_activity('Add Minutes', json_encode($data));
            return $row;
        }
    }

    public function save_attendance($minute_id, $data)
    {
        MinutesAttendance::where('minute_id', $minute_id)->delete();

        $att = $data['att'] ?? [];
        foreach ($att as $member_id => $status) {
            if (!in_array($status, ['P', 'A', 'E', 'L'], true)) continue;
            MinutesAttendance::create([
                'minute_id' => (int) $minute_id,
                'member_id' => (int) $member_id,
                'status' => $status,
            ]);
        }
    }

    public function delete($recordid)
    {
        Minutes::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);     
        log_activity('Delete Minutes', json_encode(Minutes::where(['id' => $recordid])->first()->toArray()));              

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted minute.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('minutes.list'); 
    }

    public function move_to_archive($recordid)
    {
        Minutes::where(['id' => $recordid])
            ->update([                          
                'is_archived'=> 1,                                 
            ]);    
        log_activity('Move to Archive - Minutes', json_encode(Minutes::where(['id' => $recordid])->first()->toArray()));              

        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully archived minute.'
        );              
        session()->flash('messages',$this->messages);
        
        return redirect()->route('minutes.list'); 
    }

    public function upload_supporting_documents(Request $request)
    {
        $request->validate([
           // 'files.*' => 'required|mimes:jpg,png,pdf,docx|max:2048'
        ]);
       
        if ($request->hasFile('filepond')) {
            $file = $request->file('filepond');
            $filename = time(). '-' . $file->getClientOriginalName();
            upload_disk()->putFileAs('uploads_minutes', $file, $filename);
            $path = upload_url('uploads_minutes', $filename);
    
            $data = [                            
                'filename'=> $filename,
                'minute_id' => $request->minute_id,
            ];
            MinutesDocuments::create($data);       

            log_activity('Add Supporting Documents - Minutes', json_encode($data));       

            return response()->json(['path' => $path]);
           
        }       
    }

    public function generate_pdf($transid)
    {
        log_activity('Generate PDF - Minutes', $transid);   
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        //echo storage_path('fonts');
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

        $minute_info = Minutes::where('id', $transid)->first();       
        $filename = "";        
        if($minute_info)
        {            
            $html = "<div class='ql-editor'>".$minute_info->editor_content."</div>";
            $filename = $minute_info->series_number;
        }            
        else 
          $html = "<h2>File not found.</h2>";    
        
        $mpdf->WriteHTML($html);

        // Attendance sheet page (roster + guests)
        if ($minute_info) {
            $attendees = MinutesAttendance::where('minute_id', $transid)
                ->join('members', 'members.id', '=', 'minutes_attendance.member_id')
                ->orderBy('members.name')
                ->get(['members.name', 'members.position', 'minutes_attendance.status']);

            $tally = ['P' => 0, 'A' => 0, 'E' => 0, 'L' => 0];
            foreach ($attendees as $a) {
                if (isset($tally[$a->status])) $tally[$a->status]++;
            }

            $sheetHtml = view('minutes._attendance_sheet', [
                'info' => $minute_info,
                'attendees' => $attendees,
                'tally' => $tally,
            ])->render();

            $mpdf->AddPage();
            $mpdf->WriteHTML($sheetHtml);
        }

        // ADD ATTACHEMENTS
        $images = array();
        $all_attachments = MinutesDocuments::where("minute_id", $transid)->where('is_deleted', 0)->orderBy('updated_at','asc')->get();
        foreach ($all_attachments as $item)
        {
            $images[] = upload_local_copy('uploads_minutes', $item->filename);
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

    public function view_minute($id)
    {
        //log_activity('View Minutes', $id);    
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            echo "<h2>Invalid File URL.</h2>";
        }

        $this->generate_pdf($id);
    }

    public function attendance_report(Request $request)
    {
        log_activity('View Attendance Report');

        $from = $request->input('from', date('Y-01-01'));
        $to   = $request->input('to', date('Y-m-d'));
        $type = $request->input('type', '');

        $mq = Minutes::where('is_deleted', 0)
            ->where('is_archived', 0)
            ->whereDate('date_created', '>=', $from)
            ->whereDate('date_created', '<=', $to);
        if ($type !== '') $mq->where('category', $type);

        $minuteIds = $mq->pluck('id')->all();
        $sessionCount = count($minuteIds);

        $rows = [];
        if ($sessionCount > 0) {
            $rows = MinutesAttendance::whereIn('minute_id', $minuteIds)
                ->get(['member_id', 'status'])
                ->map(fn($r) => ['member_id' => $r->member_id, 'status' => $r->status])
                ->all();
        }

        $members = Member::where('is_deleted', 0)->orderBy('name')
            ->get(['id', 'name', 'position'])
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'position' => $m->position])
            ->all();

        $summary = AttendanceReport::summarize($rows, $members, $sessionCount);

        if ($request->input('export') === 'csv') {
            return $this->export_report_csv($summary, $from, $to, $type);
        }

        $sessionTypes = ['Regular Session', 'Committee Hearing', 'Special Session'];

        return view('minutes.report', [
            'menu' => 'Minutes',
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'sessionTypes' => $sessionTypes,
        ]);
    }

    public function export_report_csv($summary, $from, $to, $type)
    {
        $filename = 'attendance_report_' . $from . '_to_' . $to . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($summary, $from, $to, $type) {
            $safe = function ($v) {
                $v = (string) $v;
                return (isset($v[0]) && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true)) ? "'".$v : $v;
            };
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Attendance Report']);
            fputcsv($out, ['Range', $from . ' to ' . $to]);
            fputcsv($out, ['Session type', $type === '' ? 'All types' : $type]);
            fputcsv($out, ['Sessions held', $summary['totals']['sessions']]);
            fputcsv($out, []);
            fputcsv($out, ['Member', 'Position', 'Present', 'Late', 'Excused', 'Absent', 'Present rate %']);
            foreach ($summary['members'] as $m) {
                fputcsv($out, [$safe($m['name']), $safe($m['position']), $m['P'], $m['L'], $m['E'], $m['A'], $m['present_rate']]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function delete_uploaded_file_view($recordid)
    {
        MinutesDocuments::where(['id' => $recordid])
            ->update([                          
                'is_deleted'=> 1,                                 
            ]);      

        log_activity('Minutes - Delete Uploaded File', json_encode(MinutesDocuments::where(['id' => $recordid])->first()->toArray()));  
        
        $this->messages[] = array(
                'type' => 'success',
                'text' => 'Successfully deleted uploaded document.'
        );              
        session()->flash('messages',$this->messages);
       
        $info = MinutesDocuments::where(['id' => $recordid])->first();
        return redirect()->route('minutes.edit', ['id' => $info->minute_id]);  
    }
}
