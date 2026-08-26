@extends("template",['menu' => $menu])

@section("additional_head")
<link rel="stylesheet" href="{{ asset("assets/src/plugins/src/sweetalerts2/sweetalerts2.css") }}">
<link href="{{ asset("assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css") }}" rel="stylesheet" type="text/css" />
<style>
    .tm-table td.tm-group { background: rgba(66,122,181,0.08); color: var(--tm-primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.04em; }
    .tm-ac-card { max-width: 720px; }
</style>
@endsection

@section("content")

<div class="layout-px-spacing">
    <div class="tm-page">

        <div class="tm-page-head">
            <div>
                <h1 class="tm-title">Access Control</h1>
                <div class="tm-crumb"><a href="{{ url('system/access_control') }}">User Management</a> / Access Control</div>
            </div>
        </div>

        <form method="post" action="{{ url('/system/select_access_control/') }}" id="frmselecttype" style="margin-bottom:16px">
            @csrf
            @php $acLabel = $selected_user_type ?: 'Select Account Type'; @endphp
            <div class="tm-dd" data-tm-dropdown data-tm-submit>
                <button type="button" class="tm-dd-toggle">
                    <span class="tm-dd-label">{{ $acLabel }}</span>
                    <svg class="tm-dd-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="tm-dd-menu">
                    @foreach($mgatypes as $item)
                        <a class="tm-dd-option {{ $selected_user_type==$item->account_type ? 'active' : '' }}" data-value="{{ $item->account_type }}">{{ $item->account_type }}</a>
                    @endforeach
                </div>
                <input type="hidden" name="user_type" value="{{ $selected_user_type }}">
            </div>
        </form>

        @if($selected_user_type!='')
            <form method="post" action="{{ url('/system/save_access_control/') }}" id="frmsaveac">
                @csrf
                <input type="hidden" name="user_type" value="{{ $selected_user_type }}" />

                <div class="tm-card tm-ac-card">
                    <table class="tm-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>User Privilege</th>
                                <th style="text-align:center">w/ Access?</th>
                            </tr>
                        </thead>
                        <tbody>
                                            {{-- RESOLUTIONS--}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    Resolutions                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Resolutions                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Resolutions" {{ in_array("View Resolutions", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Add Resolution                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Add Resolution" {{ in_array("Add Resolution", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Edit Resolution                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Edit Resolution" {{ in_array("Edit Resolution", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            

                                            <tr>
                                                <td>
                                                    Archive Resolution                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Archive Resolution" {{ in_array("Archive Resolution", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>    
                                            
                                            {{-- ORDINANCES--}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    Ordinances                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Ordinances                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Ordinances" {{ in_array("View Ordinances", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Add Ordinance                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Add Ordinance" {{ in_array("Add Ordinance", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Edit Ordinance                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Edit Ordinance" {{ in_array("Edit Ordinance", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Archive Ordinance                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Archive Ordinance" {{ in_array("Archive Ordinance", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>    

                                            <tr>
                                                <td>
                                                    Manage Ordinance Types                                  
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Manage Ordinance Types" {{ in_array("Manage Ordinance Types", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>    

                                            {{-- MINUTES--}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    MINUTES                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Minutes                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Minutes" {{ in_array("View Minutes", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Add Minute                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Add Minute" {{ in_array("Add Minute", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Edit Minute                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Edit Minute" {{ in_array("Edit Minute", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Archive Minute                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Archive Minute" {{ in_array("Archive Minute", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>  
                                            
                                            {{-- Communications--}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    Communications                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Communications                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Communications" {{ in_array("View Communications", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Add Communication                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Add Communication" {{ in_array("Add Communication", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Edit Communication                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Edit Communication" {{ in_array("Edit Communication", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Archive Communication                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Archive Communication" {{ in_array("Archive Communication", $user_permissions) ? 'checked' : '' }}>                                               
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>    


                                            {{-- SANGGUNIANG ACTIVITIES --}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    Sangguniang Activities                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Activities                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Activities" {{ in_array("View Activities", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Add Activity                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Add Activity" {{ in_array("Add Activity", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Edit Activity                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Edit Activity" {{ in_array("Edit Activity", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Archive Activity                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Archive Activity" {{ in_array("Archive Activity", $user_permissions) ? 'checked' : '' }}>                                               
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>    


                                            {{-- SEARCH--}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    Global Search                                        
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    Search Document                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Search Document" {{ in_array("Search Document", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                             {{-- SEARCH--}}
                                             <tr>    
                                                <td colspan="2" class="tm-group">
                                                    Archive                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Archives                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Archives" {{ in_array("View Archives", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            {{-- MINUTES--}}
                                            <tr>    
                                                <td colspan="2" class="tm-group">
                                                    User Management                                         
                                                </td>  
                                            </tr>
                                            <tr>
                                                <td>
                                                    View Logs                                       
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Logs" {{ in_array("View Logs", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    View Users                                    
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="View Users" {{ in_array("View Users", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Add User                                   
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Add User" {{ in_array("Add User", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Edit User                                  
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Edit User" {{ in_array("Edit User", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Reset Password                                   
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Reset Password" {{ in_array("Reset Password", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Archive User                                  
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Archive User" {{ in_array("Archive User", $user_permissions) ? 'checked' : '' }}>
                                                    
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>

                                            <tr>
                                                <td>
                                                    Set Access Control                                      
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Set Access Control" {{ in_array("Set Access Control", $user_permissions) ? 'checked' : '' }}>                                               
                                                    </div>
                                                </td>                                                                                                                                             
                                            </tr>      
                                            
                                            <tr>
                                                <td>
                                                    Signatories                                   
                                                </td>     
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Signatories" {{ in_array("Signatories", $user_permissions) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    Members
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-check-primary form-check-inline pe-2">
                                                        <input class="form-check-input" type="checkbox" name="privileges[]" value="Members" {{ in_array("Members", $user_permissions) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="btnsave" value="1" class="tm-btn tm-btn-primary mt-3">Save Changes</button>
            </form>
        @endif

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

    function view_details(param)
    {                                        
        $.ajax({
                type: 'POST',
                data: {record_id: param},
                dataType: "json",
                url:'{{ url("system/get_log_details") }}',
                success: function (data){			     
                    jsonString = data['record_info']['description'];                                                                                                                     
                    if(jsonString=='')
                    {
                        let tableBody = document.querySelector("#tbldetails tbody");
                        tableBody.innerHTML = "";
                        let tr = document.createElement("tr"); // Create a row

                        let tdKey = document.createElement("td"); // First column (key)
                        tdKey.textContent = 'none';

                        let tdValue = document.createElement("td"); // Second column (value)
                        tdValue.textContent = 'none';

                        tr.appendChild(tdKey);
                        tr.appendChild(tdValue);

                        tableBody.appendChild(tr); // Append row to table
                    }
                    else
                    {
                        let data = JSON.parse(jsonString);        
                        // Get table body
                        let tableBody = document.querySelector("#tbldetails tbody");
                        tableBody.innerHTML = "";

                        // Loop through key-value pairs and add rows
                        Object.entries(data).forEach(([key, value]) => {
                            if(key=='editor_content') return;

                            let tr = document.createElement("tr"); // Create a row

                            let tdKey = document.createElement("td"); // First column (key)
                            tdKey.textContent = key;

                            let tdValue = document.createElement("td"); // Second column (value)
                            tdValue.textContent = value;

                            tr.appendChild(tdKey);
                            tr.appendChild(tdValue);

                            tableBody.appendChild(tr); // Append row to table
                        });
                    }         
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