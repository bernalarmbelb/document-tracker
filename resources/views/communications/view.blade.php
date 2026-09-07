@extends("template",['menu' => $menu])

@section("additional_head")
<style>
    .tm-kv { font-size: 13px; }
    .tm-kv .r { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--tm-line); }
    .tm-kv .r:last-child { border-bottom: none; }
    .tm-kv .k { color: var(--tm-muted); white-space: nowrap; }
    .tm-kv .v { text-align: right; font-weight: 600; max-width: 65%; }
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
                <h1 class="tm-title">Communication</h1>
                <div class="tm-crumb"><a href="{{ url('communications/incoming_list') }}">Communications</a> / View</div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="tm-card">
                    @if($info->communication_type=='INCOMING')
                        <h3><span class="tm-badge tm-badge-info">INCOMING</span></h3>
                        <p style="font-weight:600;font-size:14px;margin-bottom:14px">Source: {{ $info->source }}</p>
                        <div class="tm-kv">
                            <div class="r"><span class="k">Date Received</span><span class="v">{{ date("M d, Y", strtotime($info->date_received)) }}</span></div>
                            <div class="r"><span class="k">Received by</span><span class="v">{{ $info->received_by }}</span></div>
                            <div class="r"><span class="k">Particulars</span><span class="v">{{ $info->particulars }}</span></div>
                            <div class="r"><span class="k">Actions Taken</span><span class="v">{{ $info->actions_taken }}</span></div>
                            <div class="r"><span class="k">Created</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->created_at)) }}</span></div>
                        </div>
                    @else
                        <h3><span class="tm-badge tm-badge-sec">OUTGOING</span></h3>
                        <p style="font-weight:600;font-size:14px;margin-bottom:14px">Addressee: {{ $info->addressee }}</p>
                        <div class="tm-kv">
                            <div class="r"><span class="k">Date Released</span><span class="v">{{ date("M d, Y", strtotime($info->date_released)) }}</span></div>
                            <div class="r"><span class="k">Released by</span><span class="v">{{ $info->released_by }}</span></div>
                            <div class="r"><span class="k">Particulars</span><span class="v">{{ $info->particulars }}</span></div>
                            <div class="r"><span class="k">Created</span><span class="v">{{ date("M d, Y h:iA", strtotime($info->created_at)) }}</span></div>
                        </div>
                    @endif
                </div>

                @if(count($all_documents)>0)
                    <div class="tm-card" style="margin-top:16px">
                        <h3>Supporting Documents</h3>
                        <ul class="tm-doclist">
                            @foreach($all_documents as $item)
                                <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--tm-muted);flex-shrink:0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                    <a href="{{ url('uploads_communications/'.$item->filename) }}" target="_blank">{{ substr($item->filename,11) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
