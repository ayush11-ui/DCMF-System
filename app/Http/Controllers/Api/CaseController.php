<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseFile;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\CaseUpdated;
use App\Events\NotificationReceived;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        $query = CaseFile::with(['judge', 'lawyer']);

        $user = $request->user();
        
        // Role-based scoping
        if ($user->role === 'judge') {
            $query->where('assigned_judge_id', $user->id);
        } elseif ($user->role === 'lawyer') {
            $query->where('assigned_lawyer_id', $user->id);
        } elseif ($user->role === 'client') {
            $query->where(function($q) use ($user) {
                $q->where('petitioner', 'like', '%'.$user->name.'%')
                  ->orWhere('respondent', 'like', '%'.$user->name.'%');
            });
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('complexity_level')) {
            $query->where('complexity_level', $request->complexity_level);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('case_number', 'like', '%'.$request->search.'%')
                  ->orWhere('title', 'like', '%'.$request->search.'%')
                  ->orWhere('petitioner', 'like', '%'.$request->search.'%')
                  ->orWhere('respondent', 'like', '%'.$request->search.'%');
            });
        }

        $cases = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $cases,
            'message' => 'Cases retrieved'
        ]);
    }

    public function show($id)
    {
        $case = CaseFile::with(['judge', 'lawyer', 'hearings.judge', 'creator'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $case,
            'message' => 'Case detail retrieved'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'case_type' => 'required|string',
            'complexity_level' => 'required|in:Simple,Standard,Complex',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'assigned_judge_id' => 'nullable|exists:users,id',
            'assigned_lawyer_id' => 'nullable|exists:users,id',
            'filing_date' => 'required|date',
            'petitioner' => 'required|string',
            'respondent' => 'required|string',
            'court_name' => 'required|string',
            'notes' => 'nullable|string',
            'priority_overridden' => 'boolean',
            'priority_override_reason' => 'nullable|string',
        ]);

        // DCFM Core Logic
        $intervalMap = ['Simple' => 14, 'Standard' => 30, 'Complex' => 45];
        $priorityModifier = ['Urgent' => 0.5, 'High' => 0.75, 'Medium' => 1.0, 'Low' => 1.25];
        
        $baseInterval = $intervalMap[$validated['complexity_level']];
        $modifier = $priorityModifier[$validated['priority']];
        $validated['hearing_interval_days'] = round($baseInterval * $modifier);
        
        $estimatedMap = ['Simple' => 3, 'Standard' => 5, 'Complex' => 8];
        $validated['estimated_hearings'] = $estimatedMap[$validated['complexity_level']];

        $validated['case_number'] = CaseFile::generateCaseNumber();
        $validated['created_by'] = $request->user()->id;

        DB::beginTransaction();
        try {
            $case = CaseFile::create($validated);
            
            // Create Notifications
            if ($case->assigned_judge_id) {
                $notification = \App\Models\DcfmNotification::create([
                    'user_id' => $case->assigned_judge_id,
                    'case_id' => $case->id,
                    'title' => 'New Case Assigned',
                    'message' => "You have been assigned as the presiding judge for case: {$case->case_number}",
                    'type' => 'assignment',
                ]);
                event(new NotificationReceived($notification));
            }

            if ($case->assigned_lawyer_id) {
                $notification = \App\Models\DcfmNotification::create([
                    'user_id' => $case->assigned_lawyer_id,
                    'case_id' => $case->id,
                    'title' => 'New Case Assignment',
                    'message' => "You have been recorded as the lawyer for case: {$case->case_number}",
                    'type' => 'assignment',
                ]);
                event(new NotificationReceived($notification));
            }

            event(new CaseUpdated($case));

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'created',
                'entity_type' => 'CaseFile',
                'entity_id' => $case->id,
                'description' => 'Created case ' . $case->case_number,
                'new_values' => $case->toArray(),
            ]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $case,
                'message' => 'Case created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create case: ' . $e->getMessage()
            ], 500);
        }
    }
}
