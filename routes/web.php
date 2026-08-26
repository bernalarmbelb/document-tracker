<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ResolutionsController;
use App\Http\Controllers\OrdinancesController;
use App\Http\Controllers\MinutesController;
use App\Http\Controllers\IncomingCommunicationsController;
use App\Http\Controllers\OutgoingCommunicationsController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SignatoriesController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\OrdinanceTypeController;
use App\Http\Controllers\SangguniangActivitiesController;

Route::get('/', [LoginController::class, 'login'])->name('login');
Route::get('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/check_login', [LoginController::class, 'check_login']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [LoginController::class, 'dashboard']);   

    //RESOLUTIONS
    Route::get('/resolutions/list', [ResolutionsController::class, 'list'])->name("resolutions.list");
    Route::post('/resolutions/list_filter', [ResolutionsController::class, 'list_filter'])->name("resolutions.list_filter");
    Route::get('/resolutions/add', [ResolutionsController::class, 'add'])->name("resolutions.add");
    Route::get('/resolutions/edit/{id}', [ResolutionsController::class, 'edit'])->name("resolutions.edit");
    Route::get('/resolutions/view/{id}', [ResolutionsController::class, 'view'])->name("resolutions.view");
    Route::get('/resolutions/delete/{id}', [ResolutionsController::class, 'delete'])->name("resolutions.delete");
    Route::get('/resolutions/move_to_archive/{id}', [ResolutionsController::class, 'move_to_archive'])->name("resolutions.move_to_archive");
    Route::get('/resolutions/generate_pdf/{id}', [ResolutionsController::class, 'generate_pdf'])->name("resolutions.generate_pdf");
    Route::post('/resolutions/get_uploaded_files', [ResolutionsController::class, 'get_uploaded_files'])->name("resolutions.get_uploaded_files");
    Route::get('/resolutions/delete_uploaded_file/{id}', [ResolutionsController::class, 'delete_uploaded_file'])->name("resolutions.delete_uploaded_file");
    Route::get('/resolutions/delete_uploaded_file_view/{id1}', [ResolutionsController::class, 'delete_uploaded_file_view'])->name("resolutions.delete_uploaded_file_view");

    Route::post('/resolutions/save_add', [ResolutionsController::class, 'save_add'])->name("resolutions.save_add");
    Route::post('/resolutions/save_changes', [ResolutionsController::class, 'save_changes'])->name("resolutions.save_changes"); 
    Route::post('/resolutions/upload_supporting_documents', [ResolutionsController::class, 'upload_supporting_documents'])->name("resolutions.upload_supporting_documents");  
    Route::post('/resolutions/upload_supporting_documents_single', [ResolutionsController::class, 'upload_supporting_documents_single'])->name("resolutions.upload_supporting_documents_single");  
    Route::get('/resolutions/print_list', [ResolutionsController::class, 'print_list'])->name("resolutions.print_list");
    
    //ORDINANCES
    Route::get('/ordinances/list', [OrdinancesController::class, 'list'])->name("ordinances.list");
    Route::post('/ordinances/list_filter', [OrdinancesController::class, 'list_filter'])->name("ordinances.list_filter");
    Route::get('/ordinances/add', [OrdinancesController::class, 'add'])->name("ordinances.add");
    Route::get('/ordinances/edit/{id}', [OrdinancesController::class, 'edit'])->name("ordinances.edit");
    Route::get('/ordinances/view/{id}', [OrdinancesController::class, 'view'])->name("ordinances.view");
    Route::get('/ordinances/delete/{id}', [OrdinancesController::class, 'delete'])->name("ordinances.delete");
    Route::get('/ordinances/move_to_archive/{id}', [OrdinancesController::class, 'move_to_archive'])->name("ordinances.move_to_archive");
    Route::get('/ordinances/generate_pdf/{id}', [OrdinancesController::class, 'generate_pdf'])->name("ordinances.generate_pdf");
    Route::post('/ordinances/get_uploaded_files', [OrdinancesController::class, 'get_uploaded_files'])->name("ordinances.get_uploaded_files");
    Route::get('/ordinances/delete_uploaded_file/{id}', [OrdinancesController::class, 'delete_uploaded_file'])->name("ordinances.delete_uploaded_file");
    Route::get('/ordinances/delete_uploaded_file_view/{id2}', [OrdinancesController::class, 'delete_uploaded_file_view'])->name("ordinances.delete_uploaded_file_view");

    Route::post('/ordinances/save_add', [OrdinancesController::class, 'save_add'])->name("ordinances.save_add");
    Route::post('/ordinances/save_changes', [OrdinancesController::class, 'save_changes'])->name("ordinances.save_changes");  
    Route::post('/ordinances/upload_supporting_documents', [OrdinancesController::class, 'upload_supporting_documents'])->name("ordinances.upload_supporting_documents");  
    Route::post('/ordinances/upload_supporting_documents_single', [OrdinancesController::class, 'upload_supporting_documents_single'])->name("ordinances.upload_supporting_documents_single");  
    Route::get('/ordinances/print_list', [OrdinancesController::class, 'print_list'])->name("ordinances.print_list");

    //ORDINANCE TYPES
    Route::get('/ordinance_type/list', [OrdinanceTypeController::class, 'list'])->name('ordinance_type.list');
    Route::get('/ordinance_type/toggle_visibility/{id}', [OrdinanceTypeController::class, 'toggle_visibility'])->name('ordinance_type.toggle_visibility');
    Route::post('/ordinance_type/save_add_type', [OrdinanceTypeController::class, 'save_add_type'])->name('ordinance_type.save_add_type');
    Route::post('/ordinance_type/save_changes_type', [OrdinanceTypeController::class, 'save_changes_type'])->name('ordinance_type.save_changes_type');
   
    //MINUTES
    Route::get('/minutes/list', [MinutesController::class, 'list'])->name("minutes.list");
    Route::get('/minutes/list_grid', [MinutesController::class, 'list_grid'])->name("minutes.list_grid");
    Route::get('/minutes/attendance_report', [MinutesController::class, 'attendance_report'])->name('minutes.attendance_report');
    Route::get('/minutes/add/{id}', [MinutesController::class, 'add'])->name("minutes.add");
    Route::get('/minutes/edit/{id}', [MinutesController::class, 'edit'])->name("minutes.edit");
    Route::get('/minutes/view/{id}', [MinutesController::class, 'view'])->name("minutes.view");
    Route::get('/minutes/delete/{id}', [MinutesController::class, 'delete'])->name("minutes.delete");
    Route::get('/minutes/move_to_archive/{id}', [MinutesController::class, 'move_to_archive'])->name("minutes.move_to_archive");
    Route::get('/minutes/generate_pdf/{id}', [MinutesController::class, 'generate_pdf'])->name("minutes.generate_pdf");
    Route::get('/minutes/delete_uploaded_file_view/{id1}', [MinutesController::class, 'delete_uploaded_file_view'])->name("minutes.delete_uploaded_file_view");

    Route::post('/minutes/save_add', [MinutesController::class, 'save_add'])->name("minutes.save_add");
    Route::post('/minutes/save_changes', [MinutesController::class, 'save_changes'])->name("minutes.save_changes");  
    Route::post('/minutes/upload_supporting_documents', [MinutesController::class, 'upload_supporting_documents'])->name("minutes.upload_supporting_documents");  
    Route::post('/minutes/search', [MinutesController::class, 'search'])->name("minutes.search");
    Route::post('/minutes/search_grid', [MinutesController::class, 'search_grid'])->name("minutes.search_grid");

    //INCOMING COMMUNICATIONS
    Route::get('/communications/incoming_list', [IncomingCommunicationsController::class, 'incoming_list'])->name("communications.incoming_list");   
    Route::post('/communications/save_add_incoming', [IncomingCommunicationsController::class, 'save_add_incoming'])->name("communications.save_add_incoming");
    Route::post('/communications/save_changes_incoming', [IncomingCommunicationsController::class, 'save_changes_incoming'])->name("communications.save_changes_incoming");
    Route::get('/communications/move_to_archive_incoming/{id}', [IncomingCommunicationsController::class, 'move_to_archive_incoming'])->name("communications.move_to_archive_incoming");
    Route::get('/communications/print_incoming', [IncomingCommunicationsController::class, 'print_incoming'])->name("communications.print_incoming");   
    Route::post('/communications/get_incoming_info', [IncomingCommunicationsController::class, 'get_incoming_info'])->name("communications.get_incoming_info");
    Route::post('/communications/upload_supporting_documents_incoming', [IncomingCommunicationsController::class, 'upload_supporting_documents_incoming'])->name("communications.upload_supporting_documents_incoming");
    Route::post('/communications/get_incoming_files', [IncomingCommunicationsController::class, 'get_incoming_files'])->name("communications.get_incoming_files");
    Route::get('/communications/view/{id}', [IncomingCommunicationsController::class, 'view'])->name("communications.view");
    Route::get('/communications/delete_uploaded_file_incoming/{id}', [IncomingCommunicationsController::class, 'delete_uploaded_file_incoming'])->name("communications.delete_uploaded_file_incoming");

    //OUTGOING COMMUNICATIONS
    Route::get('/communications/outgoing_list', [OutgoingCommunicationsController::class, 'outgoing_list'])->name("communications.outgoing_list");
    Route::post('/communications/save_add_outgoing', [OutgoingCommunicationsController::class, 'save_add_outgoing'])->name("communications.save_add_outgoing");
    Route::post('/communications/save_changes_outgoing', [OutgoingCommunicationsController::class, 'save_changes_outgoing'])->name("communications.save_changes_outgoing");
    Route::get('/communications/move_to_archive_outgoing/{id}', [OutgoingCommunicationsController::class, 'move_to_archive_outgoing'])->name("communications.move_to_archive_outgoing");
    Route::get('/communications/print_outgoing', [OutgoingCommunicationsController::class, 'print_outgoing'])->name("communications.print_outgoing");  
    Route::post('/communications/get_outgoing_info', [OutgoingCommunicationsController::class, 'get_outgoing_info'])->name("communications.get_outgoing_info");
    Route::post('/communications/upload_supporting_documents_outgoing', [OutgoingCommunicationsController::class, 'upload_supporting_documents_outgoing'])->name("communications.upload_supporting_documents_outgoing");
    Route::post('/communications/get_outgoing_files', [OutgoingCommunicationsController::class, 'get_outgoing_files'])->name("communications.get_outgoing_files");
    Route::get('/communications/delete_uploaded_file_outgoing/{id}', [OutgoingCommunicationsController::class, 'delete_uploaded_file_outgoing'])->name("communications.delete_uploaded_file_outgoing");

    //SYSTEM
    Route::get('/system/logs', [SystemController::class, 'logs'])->name("system.logs");
    Route::post('/system/logs_filter', [SystemController::class, 'logs_filter'])->name("system.logs_filter");
    Route::post('/system/get_log_details', [SystemController::class, 'get_log_details'])->name("system.get_log_details");
    Route::get('/system/user_list', [SystemController::class, 'user_list'])->name("system.user_list");
    Route::post('/system/save_add_user', [SystemController::class, 'save_add_user'])->name("system.save_add_user");
    Route::post('/system/reset_password', [SystemController::class, 'reset_password'])->name("system.reset_password");
    Route::get('/system/move_to_archive/{id}', [SystemController::class, 'move_to_archive'])->name("system.move_to_archive");
    Route::post('/system/get_user_info', [SystemController::class, 'get_user_info'])->name("system.get_user_info");
    Route::post('/system/save_changes_user', [SystemController::class, 'save_changes_user'])->name("system.save_changes_user");
    Route::get('/system/access_control', [SystemController::class, 'access_control'])->name("system.access_control");
    Route::post('/system/select_access_control', [SystemController::class, 'select_access_control'])->name("system.select_access_control");
    Route::post('/system/save_access_control', [SystemController::class, 'save_access_control'])->name("system.save_access_control");
    Route::get('/system/print_logs_list', [SystemController::class, 'print_logs_list'])->name("system.print_logs_list");
    
    //ARCHIVE
    Route::get('/archive/resolutions', [ArchiveController::class, 'resolutions'])->name("archive.resolutions");
    Route::get('/archive/ordinances', [ArchiveController::class, 'ordinances'])->name("archive.ordinances");
    Route::get('/archive/minutes', [ArchiveController::class, 'minutes'])->name("archive.minutes");
    Route::get('/archive/communications', [ArchiveController::class, 'communications'])->name("archive.communications");
    Route::get('/archive/users', [ArchiveController::class, 'users'])->name("archive.users");
    Route::get('/archive/sangguniang', [ArchiveController::class, 'sangguniang'])->name("archive.sangguniang");

    Route::get('/archive/resolutions_unarchive/{id}', [ArchiveController::class, 'resolutions_unarchive'])->name("archive.resolutions_unarchive");
    Route::get('/archive/ordinances_unarchive/{id}', [ArchiveController::class, 'ordinances_unarchive'])->name("archive.ordinances_unarchive");
    Route::get('/archive/minutes_unarchive/{id}', [ArchiveController::class, 'minutes_unarchive'])->name("archive.minutes_unarchive");
    Route::get('/archive/communications_unarchive/{id}', [ArchiveController::class, 'communications_unarchive'])->name("archive.communications_unarchive");
    Route::get('/archive/users_unarchive/{id}', [ArchiveController::class, 'users_unarchive'])->name("archive.users_unarchive");
    Route::get('/archive/sangguniang_unarchive/{id}', [ArchiveController::class, 'sangguniang_unarchive'])->name("archive.sangguniang_unarchive");
    
    //GLOBAL SEARCH    
    Route::post('/global_search', [SearchController::class, 'global_search'])->name("search.global_search");

    //SIGNATORIES
    Route::get('/signatories/list', [SignatoriesController::class, 'list'])->name('signatories.list');
    Route::get('/signatories/toggle_visibility/{id}', [SignatoriesController::class, 'toggle_visibility'])->name('signatories.toggle_visibility');
    Route::post('/signatories/save_add_signatory', [SignatoriesController::class, 'save_add_signatory'])->name('signatories.save_add_signatory');
    Route::post('/signatories/save_changes_signatory', [SignatoriesController::class, 'save_changes_signatory'])->name('signatories.save_changes_signatory');
    Route::post('/signatories/upload_esignature', [SignatoriesController::class, 'upload_esignature'])->name("signatories.upload_esignature");

    //MEMBERS
    Route::get('/members/list', [MembersController::class, 'list'])->name('members.list');
    Route::get('/members/toggle_visibility/{id}', [MembersController::class, 'toggle_visibility'])->name('members.toggle_visibility');
    Route::post('/members/save_add', [MembersController::class, 'save_add'])->name('members.save_add');
    Route::post('/members/save_changes', [MembersController::class, 'save_changes'])->name('members.save_changes');

    //SANGGUNIANG ACTIVITIES
    Route::get('/activities/all', [SangguniangActivitiesController::class, 'all'])->name("activities.all");
    Route::get('/activities/upcoming', [SangguniangActivitiesController::class, 'upcoming'])->name("activities.upcoming");
    Route::get('/activities/ongoing', [SangguniangActivitiesController::class, 'ongoing'])->name("activities.ongoing");
    Route::get('/activities/completed', [SangguniangActivitiesController::class, 'completed'])->name("activities.completed");   
    Route::get('/activities/add', [SangguniangActivitiesController::class, 'add'])->name("activities.add");
    Route::get('/activities/edit/{id}', [SangguniangActivitiesController::class, 'edit'])->name("activities.edit");
    Route::post('/activities/save_add', [SangguniangActivitiesController::class, 'save_add'])->name("activities.save_add");
    Route::post('/activities/save_changes', [SangguniangActivitiesController::class, 'save_changes'])->name("activities.save_changes");  
    Route::post('/activities/upload_supporting_documents', [SangguniangActivitiesController::class, 'upload_supporting_documents'])->name("activities.upload_supporting_documents");  
    Route::get('/activities/delete_uploaded_file_view/{id1}', [SangguniangActivitiesController::class, 'delete_uploaded_file_view'])->name("activities.delete_uploaded_file_view");
    Route::get('/activities/move_to_archive/{id}', [SangguniangActivitiesController::class, 'move_to_archive'])->name("activities.move_to_archive");
    Route::get('/activities/print_list/{mode}', [SangguniangActivitiesController::class, 'print_list'])->name("activities.print_list");
    Route::get('/activities/view/{id}', [SangguniangActivitiesController::class, 'view'])->name("activities.view");
});

Route::get('/view_ordinance/{id}', [OrdinancesController::class, 'view_ordinance'])->name("ordinances.view_ordinance");
Route::get('/view_resolution/{id}', [ResolutionsController::class, 'view_resolution'])->name("resolutions.view_resolution");
Route::get('/view_minute/{id}', [MinutesController::class, 'view_minute'])->name("minutes.view_minute");
Route::get('/view_activity/{id}', [SangguniangActivitiesController::class, 'view_activity'])->name("activities.view_activity");