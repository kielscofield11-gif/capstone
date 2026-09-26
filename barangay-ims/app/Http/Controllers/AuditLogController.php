<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view-audit-logs');
        $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:100'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = AuditLog::with('user');

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->string('search')->toString());
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
            if (is_numeric($search)) {
                $query->orWhere('model_id', $search);
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(50)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action')->sort();
        $models = AuditLog::select('model_type')->distinct()->pluck('model_type')->sort();
        $users = User::whereIn('id', AuditLog::whereNotNull('user_id')->select('user_id'))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('audit-logs.index', compact('logs', 'actions', 'models', 'users'));
    }
}
