<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ordinances extends Model
{
    use HasFactory;
    protected $table = 'ordinances';

    protected $fillable = [       
        'ordinance_number',
        'author_name',   
        'short_title',
        'subject_matter',
        'source_book_number',
        'sp_resolutions',
        'status',
        'publication_postings',
        'ordinance_type',
        'barangay_ordinance',
        'approved_date',
        'keywords_tags',
        'drive_location',
        'is_deleted',
        'added_by',
        'editor_content',
        'date_created',
        'is_archived',
        'ordinance_status',

    ];
}
