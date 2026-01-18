<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->paginate(50);

        // Get unique actions for filter
        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get users who have activities
        $users = \App\Models\User::whereHas('auditLogs')
            ->select('id', 'first_name', 'last_name')
            ->get();

        return view('admin.activity-log', compact('activities', 'actions', 'users'));
    }

    public function show($id)
    {
        $activity = AuditLog::with('user')->findOrFail($id);
        return view('admin.activity-show', compact('activity'));
    }
}