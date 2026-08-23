<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        $logs = ActivityLog::with('user')
            ->latest('created_at')
            ->paginate(30)
            ->through(fn (ActivityLog $log) => [
                'id' => $log->id,
                'user' => $log->user?->name ?? 'Sistem',
                'action' => $log->action,
                'description' => $log->description,
                'created_at' => $log->created_at->format('d/m/Y H:i:s'),
            ]);

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
        ]);
    }
}
