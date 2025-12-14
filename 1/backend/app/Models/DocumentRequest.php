<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'document_data',
        'document_status',
        'staff_message',
        'requestor_message',
    ];

    protected $casts = [
        'document_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
