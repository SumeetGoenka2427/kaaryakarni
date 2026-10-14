<x-layouts.admin title="Dashboard">
    <div class="admin-page-head">
        <div>
            <h2>Samaj Overview</h2>
            <p class="muted">Welcome, {{ auth('admin')->user()->name }}. Member, donation and approval statistics appear here as each module goes live.</p>
        </div>
    </div>

    <div class="grid grid-2 align-start">
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Manage the Website</h3></div>
            <div class="admin-panel-body stack">
                @can('settings.manage')
                    <a class="flex-between" href="{{ route('admin.branding.edit') }}"><span>Branding &amp; Logo</span><span aria-hidden="true">&rarr;</span></a>
                    <a class="flex-between" href="{{ route('admin.settings.edit') }}"><span>Organisation, contact &amp; membership fee</span><span aria-hidden="true">&rarr;</span></a>
                @endcan
                @can('content.manage')
                    <a class="flex-between" href="{{ route('admin.site-texts.index') }}"><span>Website texts (English / हिन्दी)</span><span aria-hidden="true">&rarr;</span></a>
                @endcan
                <a class="flex-between" href="{{ route('home') }}"><span>View public website</span><span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Recent Admin Actions</h3></div>
            <div class="admin-panel-body stack">
                @forelse ($recentActions as $log)
                    <div class="audit-row">
                        <strong>{{ $log->admin?->name ?? 'System' }}</strong>
                        <span class="muted">{{ $log->description ?? $log->action }}</span>
                        <time class="muted" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d M Y, h:i A') }}</time>
                    </div>
                @empty
                    <p class="muted">No admin actions recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
