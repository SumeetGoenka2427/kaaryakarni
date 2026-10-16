@php $f = fn ($k) => $filters[$k] ?? null; @endphp
<x-layouts.admin title="Audit Logs">
    <div class="admin-page-head">
        <div><h2>Admin Audit Log</h2><p class="muted">Read-only record of administrative actions performed across the portal, newest first. Entries cannot be edited or deleted.</p></div>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field search-field"><label for="a-q">Search</label><input type="search" id="a-q" name="q" value="{{ $f('q') }}" placeholder="Search by actor, action or details" maxlength="100"></div>
        <div class="field">
            <label for="a-admin">Admin</label>
            <select id="a-admin" name="admin" data-autosubmit>
                <option value="">All admins</option>
                @foreach ($admins as $id => $name)<option value="{{ $id }}" @selected((int) $f('admin') === $id)>{{ $name }}</option>@endforeach
            </select>
        </div>
        <div class="field">
            <label for="a-module">Area of the portal</label>
            <select id="a-module" name="module" data-autosubmit>
                <option value="">All</option>
                @foreach ($modules as $m)<option value="{{ $m }}" @selected($f('module') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>@endforeach
            </select>
        </div>
        <div class="field"><label for="a-from">From</label><input type="date" id="a-from" name="from" value="{{ $f('from') }}"></div>
        <div class="field"><label for="a-to">To</label><input type="date" id="a-to" name="to" value="{{ $f('to') }}"></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline btn-sm">Clear</a>@endif
        </div>
    </form>

    @if ($logs->isEmpty())
        <x-empty-state title="No audit log entries found" icon="search"><p>Try clearing the search or filters.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Target</th><th scope="col">Details</th><th scope="col">Date</th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td data-label="Actor">{{ $log->admin?->name ?? 'System' }}@if ($log->ip)<div class="muted">{{ $log->ip }}</div>@endif</td>
                            <td data-label="Action"><code>{{ $log->action }}</code></td>
                            <td data-label="Target">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
                            <td data-label="Details">
                                {{ $log->description ?? '—' }}
                                @if ($log->old_values || $log->new_values)
                                    <details class="audit-details">
                                        <summary>Changes</summary>
                                        <table class="diff-table">
                                            <thead><tr><th scope="col">Field</th><th scope="col">Before</th><th scope="col">After</th></tr></thead>
                                            <tbody>
                                                @foreach (array_unique(array_merge(array_keys($log->old_values ?? []), array_keys($log->new_values ?? []))) as $field)
                                                    @php
                                                        $old = $log->old_values[$field] ?? null;
                                                        $new = $log->new_values[$field] ?? null;
                                                        $show = fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (is_bool($v) ? ($v ? 'Yes' : 'No') : ($v === null || $v === '' ? '—' : (string) $v));
                                                    @endphp
                                                    <tr>
                                                        <th scope="row">{{ $field }}</th>
                                                        <td @class(['diff-old' => $old !== $new && $log->old_values])>{{ \Illuminate\Support\Str::limit($show($old), 200) }}</td>
                                                        <td @class(['diff-new' => $old !== $new])>{{ \Illuminate\Support\Str::limit($show($new), 200) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </details>
                                @endif
                            </td>
                            <td data-label="Date"><time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d M Y, h:i A') }}</time></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $logs->links() }}</div>
    @endif
</x-layouts.admin>
