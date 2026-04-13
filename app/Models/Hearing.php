<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hearing extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'judge_id',
        'hearing_date',
        'hearing_time',
        'duration_minutes',
        'status',
        'hearing_type',
        'notes',
        'outcome',
        'courtroom',
        'is_auto_scheduled',
        'scheduled_by',
    ];

    protected $casts = [
        'hearing_date' => 'date',
        'is_auto_scheduled' => 'boolean',
    ];

    public function caseFile()
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}
