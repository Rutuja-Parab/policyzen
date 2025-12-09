<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */

    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with(['performer', 'policy', 'endorsement'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        if ($request->has('entity_type') && $request->entity_type !== '') {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search !== '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('action', 'like', $searchTerm)
                  ->orWhere('entity_type', 'like', $searchTerm)
                  ->orWhere('transaction_type', 'like', $searchTerm);
            });
        }

        // Paginate results
        $auditLogs = $query->paginate(20)->withQueryString();

        // Get filter options
        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $entityTypes = AuditLog::select('entity_type')
            ->distinct()
            ->whereNotNull('entity_type')
            ->orderBy('entity_type')
            ->pluck('entity_type');

        return view('audit-logs.index', compact('auditLogs', 'actions', 'entityTypes'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['performer', 'policy', 'endorsement']);
        return view('audit-logs.show', compact('auditLog'));
    }

    /**
     * Get audit log statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('created_at', today())->count(),
            'this_week_logs' => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_logs' => AuditLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        // Most common actions
        $commonActions = AuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Recent activity (last 24 hours)
        $recentActivity = AuditLog::with(['performer'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'common_actions' => $commonActions,
            'recent_activity' => $recentActivity
        ]);
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request)
    {
        $query = AuditLog::with(['performer', 'policy', 'endorsement'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        if ($request->has('entity_type') && $request->entity_type !== '') {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $auditLogs = $query->get();

        // Return CSV data (you can implement CSV generation here)
        return response()->json([
            'message' => 'Export functionality needs to be implemented',
            'count' => $auditLogs->count()
        ]);
    }
}