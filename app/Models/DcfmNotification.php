<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DcfmNotification extends Model
{
    use HasFactory;
    
    protected $table = 'dcfm_notifications';

    protected $fillable = [
        'user_id',
        'case_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function caseFile()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }
}
