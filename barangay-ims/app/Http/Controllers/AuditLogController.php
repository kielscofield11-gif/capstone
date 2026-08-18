<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action')->sort();
        $models = AuditLog::select('model_type')->distinct()->pluck('model_type')->sort();

        return view('audit-logs.index', compact('logs', 'actions', 'models'));
    }
}
