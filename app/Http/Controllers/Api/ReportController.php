<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseFile;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $query = CaseFile::query();
        if ($user->role === 'judge') {
            $query->where('assigned_judge_id', $user->id);
        } elseif ($user->role === 'lawyer') {
            $query->where('assigned_lawyer_id', $user->id);
        }

        $total = (clone $query)->count();
        $open = (clone $query)->whereIn('status', ['Pending', 'Ongoing'])->count();
        $closed = (clone $query)->where('status', 'Closed')->count();
        $urgent = (clone $query)->where('priority', 'Urgent')->count();

        $resolutionRate = $total > 0 ? round(($closed / $total) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_cases' => $total,
                'open_cases' => $open,
                'closed_cases' => $closed,
                'urgent_cases' => $urgent,
                'resolution_rate' => $resolutionRate,
                'cases_by_type' => (clone $query)->selectRaw('case_type, count(*) as count')->groupBy('case_type')->get(),
                'cases_by_status' => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->get(),
            ],
            'message' => 'Report stats retrieved'
        ]);
    }
}
