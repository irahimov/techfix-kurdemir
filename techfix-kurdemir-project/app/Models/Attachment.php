<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'ticket_id',
        'ticket_message_id',
        'uploaded_by',
        'original_name',
        'stored_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
    ];
}