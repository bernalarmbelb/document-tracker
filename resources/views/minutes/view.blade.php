@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/editors/quill/quill.snow.css") }}">
<style>
    .tm-doc .ql-editor { padding: 0; }
    .tm-doc .ql-editor p { margin: 0; }
    .tm-doc .ql-align-center img { width: 100%; }
    .tm-kv { font-size: 13px; }
    .tm-kv .r { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--tm-line); }
    .tm-kv .r:last-child { border-bottom: none; }
    .tm-kv .k { color: var(--tm-muted); white-space: nowrap; }
    .tm-kv .v { text-align: right; font-weight: 600; }
    .tm-doclist { list-style: none; margin: 0; padding: 0; }
    .tm-doclist li { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--tm-line); font-size: 13px; }
    .tm-doclist li:last-child { border-bottom: none; }
    .tm-doclist a { color: var(--tm-primary); text-decoration: none; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Minutes {{ $info->series_number }}</h1>
                <div class="tm-crumb"><a href="{{ url('minutes/list') }}">Minutes</a> / View</div>
            </div>
            <div class="d-flex align-items-center" style="gap:10px;flex-wrap:wrap">
                @if(Auth::user()->hasPermission('Edit Minute'))
                    <a href="{{ url('minutes/edit/'.$info->id) }}" class="tm-btn tm-btn-dark">✎ Edit</a>
                @endif
                <a href="{{ url('minutes/generate_pdf/'.$info->id) }}" target="_blank" class="tm-btn tm-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Export / Print
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="tm-card tm-doc">
                    <div class="content-section ql-editor">
                        {!! $info->editor_content !!}
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="tm-stack">
                    <div class="tm-card">
                        <h3>{{ $info->series_number }}</h3>
                        <p style="font-weight:600;font-size:14px;margin-bottom:14px">{{ $info->presiding_officer }}</p>
                        <div class="tm-kv">
                            <div class="r"><span class="k">Agenda</span><span class="v">{{ $info->agenda_1 }}{{ $info->agenda_2 !='' ? ', '.$info->agenda_2 : '' }}{{ $info->agenda_3 !='' ? ', '.$info->agenda_3 : '' }}{{ $info->agenda_4 !='' ? ', '.$info->agenda_4 : '' }}{{ $info->agenda_5 !='' ? ', '.$info->agenda_5 : '' }}</span></div>
                            <div class="r"><span class="k">Venue</span><span class="v">{{ $info->venue }}</span></div>
                            <div class="r"><span class="k">Created</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->date_created)) }}</span></div>
                        </div>
                    </div>

                    @if(count($all_documents)>0)
                        <div class="tm-card">
                            <h3>Supporting Documents</h3>
                            <ul class="tm-doclist">
                                @foreach($all_documents as $item)
                                    <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--tm-muted);flex-shrink:0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                        <a href="{{ upload_url('uploads_minutes', $item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="tm-card" style="text-align:center">
                        <h3 style="justify-content:center">QR Code</h3>
                        <div class="custom-qr-code" style="display:inline-block">
                            {!! $qr_code !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
