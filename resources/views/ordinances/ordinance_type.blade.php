@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/src/table/datatable/datatables.css") }}">    
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/dt-global_style.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/dark/table/datatable/dt-global_style.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/light/table/datatable/custom_dt_custom.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset("assets/src/plugins/css/dark/table/datatable/custom_dt_custom.css") }}">

<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/assets/css/light/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/assets/css/dark/scrollspyNav.css") }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("assets/src/plugins/css/dark/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />

<link href="{{ asset("assets/src/assets/css/light/components/modal.css") }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset("assets/css/app.css") }}">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

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
</style>
@endsection

@section("content")

<div class="layout-px-spacing">

    <div class="middle-content container-xxl p-0">
      
        <div class="row layout-top-spacing">
            <div class="d-flex justify-content-between">
                <div class="ms-2 mb-4">
                    <h4 class="mb-0 page-title">ORDINANCE TYPE</h4>
                    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('ordinance_type/list') }}">Ordinance Types</a></li>
                            <li class="breadcrumb-item active" aria-current="page">List</li>
                        </ol>
                    </nav>                                          
                </div>      
                <div class="task-action">
                    <a href="\" class="btn btn-secondary _effect--ripple waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#add-type">Add New Type</a>                    
                </div>
            </div> 

            

            <div class="col-lg-6 col-md-12 layout-spacing">
                <div class="statbox widget box box-shadow">                    
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table id="zero-config" class="table dt-table-hover style-3" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Ordinance Type</th>                      
                                        <th class="text-center">Description</th>       
                                        <th class="text-center">Visible?</th>                                                                                                    
                                        <th class="text-center no-content">Actions</th>                               
                                    </tr>                                   
                                </thead>
                                <tbody>
                                    @foreach($records as $item)
                                    <tr>
                                        <td>
                                            <a href="" class="text-info">{{ strtoupper($item->ordinance_type) }}</a>                                       
                                        </td>             
                                        <td class="text-center">{{ $item->description }}</td>   
                                        <td class="text-center"> <span class="badge {{ $item->is_deleted=='0' ? 'badge-light-info' : 'badge-light-danger' }}  mb-2 me-4">{{ $item->is_deleted=='0' ? 'YES' : 'NO' }}</span></td>                                                                                                          
                                        <td class="text-start">                                            
                                            <ul class="table-controls">
                                                <li><a href="javascript:void(0);" onclick='view_details(@json($item->ordinance_type),@json($item->description))' data-bs-toggle="modal" data-bs-target="#edit-type" class="bs-tooltip text-success" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Edit" aria-label="Edit" data-bs-original-title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 p-1 br-8 mb-1 text-success"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a></li>                                                                                    
                                                <li><a href="javascript:void(0);" onclick='return confirm_toggle("{{ url("ordinance_type/toggle_visibility/".urlencode($item->ordinance_type)) }}")' class="bs-tooltip text-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Toggle Visibility" aria-label="Toggle Visibility" data-bs-original-title="Toggle Visibility"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye p-1 br-8 mb-1 text-primary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a></li>      
                                                                                            
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

<div class="modal fade" id="add-type" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Add Ordinance Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="simple-pill">                                                 
                    <form class="row g-3" action="{{ url('/ordinance_type/save_add_type/') }}" method="post" autocomplete="off">  
                        @csrf                                                                          
                       
                        <div class="col-12">
                            <label class="form-label text-sm">Ordinance Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" required name="ordinance_type" >
                        </div>

                        <div class="col-12">
                            <label class="form-label text-sm">Description <span class="text-secondary">optional</span></label>
                            <input type="text" class="form-control form-control-sm"  name="description" >
                        </div>

                        
                                                                                                                        
                                                                                              
                        <div class="d-grid gap-2 col-12 mt-4 mx-auto">
                            <button type="submit" name="btnsave" value="1" class="btn btn-block btn-primary _effect--ripple waves-effect waves-light">Save</button>
                        </div>                            
                    </form>
          </div>
          </div>
       
      </div>
    </div>
</div>

<div class="modal fade" id="edit-type" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tabsModalLabel">Edit Ordinance Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="simple-pill">                                                 
                    <form class="row g-3" action="{{ url('/ordinance_type/save_changes_type/') }}" method="post" autocomplete="off">  
                        @csrf                                                                          
                        <input type="hidden"  name="ordinance_type_id" id="ordinance_type_id" >
                        <div class="col-12">
                            <label class="form-label text-sm">Ordinance Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" required name="ordinance_type" id="ordinance_type" >
                        </div>

                        <div class="col-12">
                            <label class="form-label text-sm">Description <span class="text-secondary">optional</span></label>
                            <input type="text" class="form-control form-control-sm" name="description" id="description" >
                        </div>
                       
                                                                                                                        
                                                                                              
                        <div class="d-grid gap-2 col-12 mt-4 mx-auto">
                            <button type="submit" name="btnsave" value="1" class="btn btn-block btn-primary _effect--ripple waves-effect waves-light">Save Changes</button>
                        </div>                            
                    </form>
          </div>
          </div>
       
      </div>
    </div>
  </div>


@endsection

@section("additional_footer")
<script src="{{ asset("assets/src/plugins/src/table/datatable/datatables.js") }}"></script>
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

   

    $('#zero-config').DataTable({
        "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
    "<'table-responsive'tr>" +
    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
        "oLanguage": {
            "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
            "sInfo": "Showing page _PAGE_ of _PAGES_",
            "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            "sSearchPlaceholder": "Search...",
            "sLengthMenu": "Results :  _MENU_",
        },
        "stripeClasses": [],
        "lengthMenu": [10, 20, 50],
        "pageLength": 10 
    });

    function confirm_toggle($url)
    {
        Swal.fire({
        title: 'Are you sure?',
        text: "This will toggle the visibility of the selected type.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, hide/unhide it!'
        }).then((result) => {
            if (result.isConfirmed) {
                
                window.location.href = $url ; 
            }
        });    
    }   

    function view_details($ordinancetype, $description)
    {
        document.getElementById("ordinance_type").value = $ordinancetype;
        document.getElementById("ordinance_type_id").value = $ordinancetype;

        document.getElementById("description").value = $description;
    }
    
    


       
    
</script>
@endsection