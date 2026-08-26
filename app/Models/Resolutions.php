<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resolutions extends Model
{
    use HasFactory;
    protected $table = 'resolutions';

    protected $fillable = [
        'id',
        'series_number',
        'title',
        'author_name',
        'keywords_tags',
        'sponsor',
        'date_created',
        'committee',
        'attested_by',
        'recorded_by',
        'approved_by',    
        'approved_date',
        'book_ref_no',
        'page_ref_no',
        'total_pages',
        'minute_ref_no',
        'drive_location',
        'editor_content',
        'is_deleted',     
        'is_archived',
        'added_by',
        'resolution_status',
    ];
}
