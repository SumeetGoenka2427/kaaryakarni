<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Admin → Audit Logs (admin/audit-logs.html): read-only record of admin actions, newest first. */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'admin' => ['nullable', 'integer'],
            'module' => ['nullable', 'string', 'max:40', 'regex:/^[a-z_-]+$/'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::with('admin:id,name')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $query->where(fn ($w) => $w->where('description', 'like', $like)->orWhere('action', 'like', $like)
                    ->orWhereHas('admin', fn ($a) => $a->where('name', 'like', $like)));
            })
            ->when($filters['admin'] ?? null, fn ($q, $id) => $q->where('admin_user_id', $id))
            ->when($filters['module'] ?? null, fn ($q, $m) => $q->where('action', 'like', $m.'.%'))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->where('created_at', '<', now()->parse($d)->addDay()))
            ->latest('id')
            ->paginate(30)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'admins' => AdminUser::orderBy('name')->pluck('name', 'id'),
            'modules' => Cache::remember('audit.modules', 600, fn () => AuditLog::query()
                ->selectRaw("DISTINCT SUBSTRING_INDEX(action, '.', 1) AS module")->orderBy('module')->pluck('module')),
        ]);
    }
}
