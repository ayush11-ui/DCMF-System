<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'case_number',
        'title',
        'description',
        'case_type',
        'complexity_level',
        'priority',
        'status',
        'assigned_judge_id',
        'assigned_lawyer_id',
        'created_by',
        'filing_date',
        'next_hearing_date',
        'closed_date',
        'hearing_interval_days',
        'estimated_hearings',
        'hearings_held',
        'petitioner',
        'respondent',
        'court_name',
        'notes',
        'priority_overridden',
        'priority_override_reason',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'next_hearing_date' => 'datetime',
        'closed_date' => 'date',
        'priority_overridden' => 'boolean',
    ];

    public function judge()
    {
        return $this->belongsTo(User::class, 'assigned_judge_id');
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hearings()
    {
        return $this->hasMany(Hearing::class, 'case_id');
    }

    // Generator for standard DCFM case numbers
    public static function generateCaseNumber()
    {
        $year = date('Y');
        $lastCase = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastCase) {
            $parts = explode('-', $lastCase->case_number);
            $sequence = intval(end($parts)) + 1;
        }
        return 'DCFM-' . $year . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
