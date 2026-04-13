<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hearing;
use App\Models\CaseFile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HearingController extends Controller
{
    public function index(Request $request)
    {
        // Used for fetching JSON for FullCalendar.js
        $query = Hearing::with(['caseFile', 'judge']);
        
        $user = $request->user();
        if ($user->role === 'judge') {
            $query->where('judge_id', $user->id);
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('hearing_date', [$request->start, $request->end]);
        }

        $hearings = $query->get();
        return response()->json([
            'success' => true,
            'data' => $hearings,
            'message' => 'Hearings retrieved'
        ]);
    }

    public function autoScheduleToggle(Request $request, $caseId)
    {
        $case = CaseFile::findOrFail($caseId);
        
        // Auto-schedule logic
        $lastHearing = $case->hearings()->orderBy('hearing_date', 'desc')->first();
        $startDate = $lastHearing ? Carbon::parse($lastHearing->hearing_date) : Carbon::parse($case->filing_date);
        
        $nextDate = $startDate->addDays($case->hearing_interval_days);
        
        // Skip weekends
        while ($nextDate->isWeekend()) {
            $nextDate->addDay();
        }

        // Check judge overlap
        if ($case->assigned_judge_id) {
            $attempts = 0;
            while ($attempts < 10) {
                $conflict = Hearing::where('judge_id', $case->assigned_judge_id)
                                   ->where('hearing_date', $nextDate->format('Y-m-d'))
                                   ->exists();
                if (!$conflict) break;
                
                $nextDate->addDay();
                while ($nextDate->isWeekend()) {
                    $nextDate->addDay();
                }
                $attempts++;
            }
        }

        $hearing = Hearing::create([
            'case_id' => $case->id,
            'judge_id' => $case->assigned_judge_id,
            'hearing_date' => $nextDate->format('Y-m-d'),
            'hearing_time' => '10:30:00', // default
            'duration_minutes' => 60,
            'hearing_type' => 'Standard',
            'is_auto_scheduled' => true,
            'scheduled_by' => $request->user()->id,
            'status' => 'Scheduled',
        ]);

        // Notify Judge
        if ($case->assigned_judge_id) {
            \App\Models\DcfmNotification::create([
                'user_id' => $case->assigned_judge_id,
                'case_id' => $case->id,
                'title' => 'New Hearing Scheduled',
                'message' => "A new hearing for case {$case->case_number} has been scheduled for {$nextDate->format('Y-m-d')} at 10:30 AM.",
                'type' => 'hearing',
            ]);
        }

        // Notify Lawyer
        if ($case->assigned_lawyer_id) {
            \App\Models\DcfmNotification::create([
                'user_id' => $case->assigned_lawyer_id,
                'case_id' => $case->id,
                'title' => 'Hearing Notice',
                'message' => "Hearing scheduled for case {$case->case_number} on {$nextDate->format('Y-m-d')}.",
                'type' => 'hearing',
            ]);
        }

        $case->update(['next_hearing_date' => clone $nextDate->setTime(10, 30)]);

        return response()->json([
            'success' => true,
            'data' => $hearing,
            'message' => 'Hearing auto-scheduled for ' . $nextDate->format('Y-m-d')
        ]);
    }
}
