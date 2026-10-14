<x-layouts.admin title="Contact Message">
    <div class="admin-page-head">
        <div><h2>Message from {{ $message->name }}</h2><p class="muted">{{ $message->subjectLabel() }} · {{ $message->created_at->format('d M Y, h:i A') }}</p></div>
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline">&larr; All Messages</a>
    </div>

    <div class="admin-panel settings-panel">
        <div class="admin-panel-body stack">
            <dl class="detail-list">
                <div><dt>Name</dt><dd>{{ $message->name }}</dd></div>
                <div><dt>Mobile</dt><dd><a href="tel:+91{{ $message->mobile }}">{{ $message->mobile }}</a></dd></div>
                <div><dt>Email</dt><dd>@if ($message->email)<a href="mailto:{{ $message->email }}">{{ $message->email }}</a>@else — @endif</dd></div>
                <div><dt>Language</dt><dd>{{ $message->locale === 'hi' ? 'हिन्दी' : 'English' }}</dd></div>
                <div><dt>Status</dt><dd><x-pill :variant="$message->status === 'new' ? 'warning' : 'success'">{{ ucfirst($message->status) }}</x-pill>
                    @if ($message->resolved_at) by {{ $message->resolver?->name ?? 'admin' }} on {{ $message->resolved_at->format('d M Y') }}@endif</dd></div>
            </dl>
            <div class="message-body">{{ $message->message }}</div>
            <div class="row-actions">
                @if ($message->status === 'new')
                    <form method="POST" action="{{ route('admin.contact-messages.resolve', $message) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">Mark as Resolved</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" data-confirm="Delete this message permanently?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
