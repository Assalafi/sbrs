<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query();

        $query->filter($request->only(['action', 'user_id', 'model_type', 'date_from', 'date_to', 'search']));

        $logs = $query->orderBy('created_at', 'desc')->paginate(30);
        $users = User::orderBy('name')->get();

        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $modelTypes = AuditLog::select('model_type')->distinct()->pluck('model_type')->map(function ($type) {
            return ['full' => $type, 'name' => class_basename($type)];
        });

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'modelTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
