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
                            <li class="breadcrumb-item"><a href="{{ url('minutes/list') }}">Switch to Table View</a></li> 
                           
                        </ol>
                    </nav>                                          
                </div>    
                
                <div class="ms-2 mb-4">
                    <form class="row g-3" action="{{ url('/minutes/search_grid/') }}" method="post" autocomplete="off">  
                        @csrf  
                        <input id="t-text" type="text" style="width: 400px;" name="keyword" value="{{ $keyword }}" placeholder="Enter a keyword and press Enter." class="form-control" onkeydown="if(event.key === 'Enter') this.form.submit()"> 
                    </form>                                 
                </div>    
            </div> 

            <div class="col-lg-4 col-md-4 layout-spacing">
                <a class="card bg-light-primary" href="{{ Auth::user()->hasPermission('Add Minute') ? url('/minutes/add/1') : 'javascript:void(0)' }}" >                    
                    <div class="card-footer">
                        <div class="row py-2">
                            <div class="col-6">
                                <b>Regular Sessions</b>
                            </div>
                            <div class="col-6 text-end">
                                <p class="text-primary mb-0"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg></p>
                            </div>
                        </div>
                    </div>
                </a>
                @foreach($all_regulars as $item)
                    <div class="card style-4 mt-4">
                        <div class="card-body pt-3">                        
                            <div class="media mt-0 mb-3">
                                <div class="">
                                    <div class="avatar avatar-md me-3">
                                        <img alt="avatar" src="{{ asset('assets/src/assets/img/profile-2.jpeg') }}" class="rounded-circle">
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading mb-0">{{ $item->presiding_officer }}</h4>
                                    <p class="media-text">Presiding Officer</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="fw-semibold mb-0">{{ $item->venue }}</h5>
                                    <span class="mt-0">Venue</span>
                                </div>

                                <div class="text-end text-body-secondary">
                                    <small>{{ date("F d, Y", strtotime($item->date_created)) }}<br/>{{ date("h:iA", strtotime($item->date_created)) }}</small>
                                </div>                                                        
                            </div>
                            <p class="card-text mt-4 mb-0">{{ $item->short_description }}</p>
                            <p class="card-text mt-4 mb-0">Agenda: {{ $item->agenda_1 }}{{ $item->agenda_2 !='' ? ', '.$item->agenda_2 : '' }}{{ $item->agenda_3 !='' ? ', '.$item->agenda_3 : '' }}{{ $item->agenda_4 !='' ? ', '.$item->agenda_4 : '' }}{{ $item->agenda_5 !='' ? ', '.$item->agenda_5 : '' }}</p>
                        </div>
                        <div class="card-footer pt-0 border-0 text-center">
                            <div class="row">
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ url("minutes/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                </div>
                                @if(Auth::user()->hasPermission('Archive Minute'))
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="#" onclick='return confirm_archive("{{ url("minutes/move_to_archive/".$item->id) }}")' class="btn btn-block btn-light-danger _effect--ripple waves-effect waves-light">Archive</a>
                                    </div>    
                                @endif         
                            </div>           
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-4 layout-spacing">
                <a class="card bg-light-danger" href="{{ Auth::user()->hasPermission('Add Minute') ? url('/minutes/add/2') : 'javascript:void(0)' }}">                    
                    <div class="card-footer">
                        <div class="row py-2">
                            <div class="col-6">
                                <b>Committee Hearing</b>
                            </div>
                            <div class="col-6 text-end">
                                <p class="text-danger mb-0"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg></p>
                            </div>
                        </div>
                    </div>
                </a>
                @foreach($all_hearings as $item)
                    <div class="card style-4 mt-4">
                        <div class="card-body pt-3">                        
                            <div class="media mt-0 mb-3">
                                <div class="">
                                    <div class="avatar avatar-md me-3">
                                        <img alt="avatar" src="{{ asset('assets/src/assets/img/profile-2.jpeg') }}" class="rounded-circle">
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading mb-0">{{ $item->presiding_officer }}</h4>
                                    <p class="media-text">Presiding Officer</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="fw-semibold mb-0">{{ $item->venue }}</h5>
                                    <span class="mt-0">Venue</span>
                                </div>

                                <div class="text-end text-body-secondary">
                                    <small>{{ date("F d, Y", strtotime($item->date_created)) }}<br/>{{ date("h:iA", strtotime($item->date_created)) }}</small>
                                </div>                                                        
                            </div>
                            <p class="card-text mt-4 mb-0">{{ $item->short_description }}</p>
                            <p class="card-text mt-4 mb-0">Agenda: {{ $item->agenda_1 }}{{ $item->agenda_2 !='' ? ', '.$item->agenda_2 : '' }}{{ $item->agenda_3 !='' ? ', '.$item->agenda_3 : '' }}{{ $item->agenda_4 !='' ? ', '.$item->agenda_4 : '' }}{{ $item->agenda_5 !='' ? ', '.$item->agenda_5 : '' }}</p>
                        </div>
                        <div class="card-footer pt-0 border-0 text-center">
                            <div class="row">
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ url("minutes/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                </div>
                                @if(Auth::user()->hasPermission('Archive Minute'))
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="#" onclick='return confirm_archive("{{ url("minutes/move_to_archive/".$item->id) }}")' class="btn btn-block btn-light-danger _effect--ripple waves-effect waves-light">Archive</a>
                                    </div>    
                                @endif         
                            </div>           
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4 col-md-4 layout-spacing">
                <a class="card bg-light-success" href="{{ Auth::user()->hasPermission('Add Minute') ? url('/minutes/add/3') : 'javascript:void(0)' }}" >                    
                    <div class="card-footer">
                        <div class="row py-2">
                            <div class="col-6">
                                <b>Special Sessions</b>
                            </div>
                            <div class="col-6 text-end">
                                <p class="text-success mb-0"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg></p>
                            </div>
                        </div>
                    </div>
                </a>
                @foreach($all_specials as $item)
                    <div class="card style-4 mt-4">
                        <div class="card-body pt-3">                        
                            <div class="media mt-0 mb-3">
                                <div class="">
                                    <div class="avatar avatar-md me-3">
                                        <img alt="avatar" src="{{ asset('assets/src/assets/img/profile-2.jpeg') }}" class="rounded-circle">
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading mb-0">{{ $item->presiding_officer }}</h4>
                                    <p class="media-text">Presiding Officer</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="fw-semibold mb-0">{{ $item->venue }}</h5>
                                    <span class="mt-0">Venue</span>
                                </div>

                                <div class="text-end text-body-secondary">
                                    <small>{{ date("F d, Y", strtotime($item->date_created)) }}<br/>{{ date("h:iA", strtotime($item->date_created)) }}</small>
                                </div>                                                        
                            </div>
                            <p class="card-text mt-4 mb-0">{{ $item->short_description }}</p>
                            <p class="card-text mt-4 mb-0">Agenda: {{ $item->agenda_1 }}{{ $item->agenda_2 !='' ? ', '.$item->agenda_2 : '' }}{{ $item->agenda_3 !='' ? ', '.$item->agenda_3 : '' }}{{ $item->agenda_4 !='' ? ', '.$item->agenda_4 : '' }}{{ $item->agenda_5 !='' ? ', '.$item->agenda_5 : '' }}</p>
                        </div>
                        <div class="card-footer pt-0 border-0 text-center">
                            <div class="row">
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <a href="{{ url("minutes/view/".$item->id) }}" class="btn btn-block btn-light-primary _effect--ripple waves-effect waves-light">View</a>
                                </div>
                                @if(Auth::user()->hasPermission('Archive Minute'))
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <a href="#" onclick='return confirm_archive("{{ url("minutes/move_to_archive/".$item->id) }}")' class="btn btn-block btn-light-danger _effect--ripple waves-effect waves-light">Archive</a>
                                    </div> 
                                @endif            
                            </div>           
                        </div>
                    </div>
                @endforeach
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
                        a.href = "{{ url('uploads_communications') }}/" + item.filename;
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