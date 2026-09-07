@extends("template",['menu' => $menu])

@section("additional_head")

<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/assets/css/light/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/dark/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/dark/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset("assets/css/app.css") }}">
<style>
  .card-title{
        font-size: 14px;        
    }
    .card-title2{
        font-size: 13px;
        margin-bottom: 0;
    }
    .card {
        height: 120px;
    }

    .table > thead > tr > th {
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 900;      
    }

    .table > tbody > tr > td {
        font-size: 12px;  
        font-family: Manrope;    
    }

    .table > tbody > tr > td >span.badge {
        font-size: 10px;  
     
    }

    #zero-config_wrapper .table-responsive {
        min-height: 270px; /* or whatever height you want */
    }

    #zero-config_wrapper .table-responsive table {
        min-height: 270px; /* or whatever height you want */
    }

   
</style>
@endsection

@section("content")

<div class="layout-px-spacing">

    <div class="middle-content container-xxl p-0">
      
        <div class="row layout-top-spacing">
            <div class="d-flex justify-content-between">
                <div class="ms-2 mb-4">
                    <h4 class="mb-0 page-title">MINUTES</h4>
                    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('minutes/list_grid') }}">Switch to Grid View</a></li>                     
                        </ol>
                    </nav>                                          
                </div>    
                
                <div class="task-action d-flex align-items-center gap-2">
                    @if(Auth::user()->hasPermission('Add Minute'))
                        <a href="{{ url("/minutes/add/1") }}" class="btn btn-light-primary _effect--ripple waves-effect waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg> Regular Session</a>
                    @endif

                    @if(Auth::user()->hasPermission('Add Minute'))
                        <a href="{{ url("/minutes/add/2") }}" class="btn btn-light-danger _effect--ripple waves-effect waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg> Committee Hearing</a>
                    @endif

                     @if(Auth::user()->hasPermission('Add Minute'))
                        <a href="{{ url("/minutes/add/3") }}" class="btn btn-light-success _effect--ripple waves-effect waves-light me-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg> Special Session</a>
                    @endif

                    <form class="row g-3" action="{{ url('/minutes/search/') }}" method="post" autocomplete="off">  
                        @csrf  
                        <input id="t-text" type="text" style="width: 400px;" name="keyword" value="{{ $keyword }}" placeholder="Enter a keyword and press Enter." class="form-control" onkeydown="if(event.key === 'Enter') this.form.submit()"> 
                    </form>                                 
                </div>    
            </div> 

           <div class="col-lg-12 col-md-12 layout-spacing" >
                <div class="statbox widget box box-shadow">                    
                    <div class="widget-content widget-content-area" >
                        <div class="table-responsive"  >
                            <table id="zero-config" class="table dt-table-hover style-3" style="width:100%; {{ count($records)<=3 ? 'min-height: 400px;' : '' }}">
                                <thead>
                                    <tr>
                                        <th>Series Number</th>
                                        <th class="text-start">Date Created</th>                                      
                                        <th class="text-start">Agenda</th>
                                        
                                        <th class="text-start">Presiding Officer</th>
                                        <th class="text-start">Venue</th>
                                      
                                        <th class="text-start">Category</th>                                                                                                   
                                        <th class="text-center no-content">Actions</th>
                                     
                                    </tr>                                   
                                </thead>
                                <tbody>
                                    @foreach($records as $item)
                                    <tr>
                                        <td><a href="{{ url("minutes/view/".$item->id) }}" class='text-info'>{{ $item->series_number }}</a></td>
                                        <td class="text-start">{{ date("M d, Y", strtotime($item->date_created)) }}</td>
                                        <td class="text-start ">
                                            <div class="dropdown d-inline-block">
                                                <a class="dropdown-toggle text-info" href="#" role="button" id="elementDrodpown1" data-bs-toggle="dropdown" data-bs-display="static" aria-haspopup="true" aria-expanded="false">
                                                    {{ $item->agenda_1 }}
                                                </a>    
                                                <div class="dropdown-menu left" aria-labelledby="elementDrodpown1" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                    @if(Auth::user()->hasPermission('Edit Minute'))
                                                        <a href="{{ url("minutes/edit/".$item->id) }}" class="dropdown-item" >Edit</a>
                                                    @endif

                                                    <a href="{{ url("minutes/view/".$item->id) }}" class="dropdown-item" >View</a>
                                                    @if(Auth::user()->hasPermission('Add Minute'))
                                                        <a href="javascript:void(0);" onclick='view_files("{{ $item->id }}", @json($item->short_title))' class="dropdown-item" data-bs-toggle="modal" data-bs-target="#add-supporting-documents">Add Supporting Document</a>
                                                    @endif

                                                    @if(Auth::user()->hasPermission('Archive Minute'))
                                                        <a href="javascript:void(0);" onclick='return confirm_archive("{{ url("minutes/move_to_archive/".$item->id) }}")' class="dropdown-item" >Archive</a>   
                                                    @endif                                              
                                                </div>
                                            </div>    
                                        </td>    
                                   
                                        <td class="text-start">{{ $item->presiding_officer }}</td>    
                                        <td class="text-start">{{ $item->venue }}</td>                                                                              
                                        <td class="text-center"><span class="badge  {{ $item->category=='Regular Session' ? 'badge-light-primary' : '' }} {{ $item->category=='Special Session' ? 'badge-light-success' : '' }} {{ $item->category=='Committee Hearing' ? 'badge-light-danger' : '' }} {{ $item->category=='UNDER STUDY' ? 'badge-light-secondary' : '' }}  mb-2 me-4">{{ $item->category }}</span> </td>                                                                                       
                                        <td class="text-start">                                            
                                            <ul class="table-controls">
                                                @if(Auth::user()->hasPermission('Edit Ordinance'))
                                                    <li><a href="{{ url("ordinances/edit/".$item->id) }}" class="bs-tooltip" title="Edit" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Edit" ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>
                                                @endif
                                            
                                                <li><a href="{{ url("ordinances/view/".$item->id) }}" class="bs-tooltip text-primary" title="View" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="View" ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>
                                             
                                                @if(Auth::user()->hasPermission('Add Ordinance'))
                                                    <li><a href="javascript:void(0);" onclick='view_files("{{ $item->id }}", @json($item->short_title))' class="bs-tooltip text-info" data-bs-toggle="modal" data-bs-target="#add-supporting-documents" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Add" aria-label="Add" data-bs-original-title="Add Supporting Documents"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share p-1 br-8 mb-1 text-info"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg></a></li>                                             
                                                @endif

                                                @if(Auth::user()->hasPermission('Archive Ordinance'))
                                                    <li><a href="javascript:void(0);" onclick='return confirm_archive("{{ url("ordinances/move_to_archive/".$item->id) }}")' class="bs-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Archive" aria-label="Archive" data-bs-original-title="Archive"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive p-1 br-8 mb-1 text-danger"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg></a></li>
                                                @endif
                                            </ul>
                                        </td>                                     
                                    </tr>
                                    @endforeach
                             
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js") }}"></script>
<script src="{{ asset("js/app.js") }}"></script>
<script>
   
window.onload = function() {    
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const messages = @json(session('messages'));

        const showMessages = async () => {
            for (const message of messages) {
                await Swal.fire({
                    title: message['type'].toUpperCase(),
                    text: message['text'],
                    icon: message['type'],
                    confirmButtonText: 'OK'
                });
            }
        };

        showMessages();
    };
    
    function confirm_delete($url)
    {
        Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                'Deleted!',
                'Record has been deleted.',
                'success'
                );
                window.location.href = $url ; 
            }
        });    
    }   
    
    function confirm_archive($url)
    {
        Swal.fire({
        title: 'Are you sure?',
        text: "This action will move the record to the archive list.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                'Archived!',
                'Record has been archived.',
                'success'
                );
                window.location.href = $url ; 
            }
        });    
    }   
    
    function view_files(param, paramparticulars)
    {               
        document.getElementById('supporting_document_communication_id').value = param;
        document.getElementById('h6_add_supporting_document').innerHTML =  paramparticulars;                          
        $.ajax({
                type: 'POST',
                data: {communication_id: param},
                dataType: "json",
                url:'{{ url("communications/get_incoming_files") }}',
                success: function (data){		
                    items  = data['rows'];
                    const ul = document.getElementById('my_files');
                    ul.innerHTML = '';

                    items.forEach(item => {
                        const li = document.createElement('li');
                        li.classList.add('list-group-item');
                        li.classList.add('ps-1');

                        const a = document.createElement('a');
                        a.href = item.url;
                        a.textContent = item.filename;
                        a.target = '_blank';
                        a.title = "View File";

                        const b = document.createElement('a');
                        b.classList.add('me-2');
                        b.classList.add('text-danger');
                        b.href = "{{ url('communications/delete_uploaded_file_incoming') }}/" + item.id;
                        b.innerHTML  = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 p-1 br-8 mb-1 delete-note"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';                       
                        b.title = "Delete File";
                        b.onclick = function (event) {
                            if (!confirm("Are you sure you want to delete this file?")) {
                                event.preventDefault(); 
                            }
                        };

                        li.appendChild(b);
                        li.appendChild(a);
                        
                        ul.appendChild(li);
                    });                                                                                                           
                        
                },
                error: function(XHR, textStatus, errorThrown) 
                {
                    console.log(errorThrown);
                    console.log(XHR.responseText);
                } 
        }
        );
    }
    
</script>
@endsection