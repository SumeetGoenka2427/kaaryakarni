@php use App\Models\Broadcast; use App\Services\Notifications\Notifier; @endphp
<x-layouts.admin title="Notifications">
    <div class="admin-page-head">
        <div><h2>Notification Management</h2><p class="muted">Broadcast Samaj-wide announcements or review member-specific notifications sent by the system.</p></div>
        <button type="button" class="btn btn-primary" data-modal-open="announce-dialog">+ New Announcement</button>
    </div>

    <div class="admin-panel content-section">
        <div class="admin-panel-head"><h3>Broadcast Announcements</h3></div>
        @if ($broadcasts->isEmpty())
            <x-empty-state title="No announcements yet"><p>Publish your first Samaj-wide announcement.</p></x-empty-state>
        @else
            <div class="data-table-wrap table-flush">
                <table class="data-table">
                    <thead><tr><th scope="col">Title</th><th scope="col">Message</th><th scope="col">Type</th><th scope="col">Sent to</th><th scope="col">Date</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($broadcasts as $b)
                            <tr>
                                <td data-label="Title"><strong>{{ $b->title_en }}</strong>@if ($b->title_hi)<div class="muted">{{ $b->title_hi }}</div>@endif</td>
                                <td data-label="Message">{{ \Illuminate\Support\Str::limit($b->message_en, 120) }}</td>
                                <td data-label="Type"><x-pill variant="neutral">{{ Broadcast::TYPES[$b->type] ?? $b->type }}</x-pill></td>
                                <td data-label="Sent to">{{ $b->audienceLabel() }} · {{ number_format($b->recipients) }} member(s)@if ($b->send_sms) <span class="pill pill-success">SMS</span>@endif</td>
                                <td data-label="Date">{{ $b->created_at->format('d M Y, h:i A') }}@if ($b->sender)<div class="muted">by {{ $b->sender->name }}</div>@endif</td>
                                <td data-label="Actions">
                                    <form method="POST" action="{{ route('admin.notifications.destroy', $b) }}" data-confirm="Remove “{{ $b->title_en }}” from every member's inbox? SMS already sent cannot be recalled.">@csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $broadcasts->links() }}</div>
        @endif
    </div>

    <div class="admin-panel">
        <div class="admin-panel-head"><h3>Member-Specific Notifications</h3></div>
        <div class="admin-panel-body">
            <form method="GET" class="filters-bar">
                <div class="field search-field"><label for="n-q">Search member</label><input type="search" id="n-q" name="q" value="{{ $q }}" placeholder="Name or mobile" maxlength="80"></div>
                <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Search</button></div>
            </form>
        </div>
        @if ($messages->isEmpty())
            <x-empty-state title="No member-specific notifications"><p>System notifications sent to individual members (approvals, payments) will appear here.</p></x-empty-state>
        @else
            <div class="data-table-wrap table-flush">
                <table class="data-table">
                    <thead><tr><th scope="col">Member</th><th scope="col">Title</th><th scope="col">Message</th><th scope="col">Type</th><th scope="col">Date</th><th scope="col">Read</th></tr></thead>
                    <tbody>
                        @foreach ($messages as $n)
                            <tr>
                                <td data-label="Member">@if ($n->notifiable)<a href="{{ route('admin.members.show', $n->notifiable) }}">{{ $n->notifiable->name }}</a>@else — @endif</td>
                                <td data-label="Title">{{ __('web.notifications.titles.'.($n->data['kind'] ?? 'general'), [], 'en') }}</td>
                                <td data-label="Message">{{ \Illuminate\Support\Str::limit($n->data['message_en'] ?? '', 120) }}</td>
                                <td data-label="Type"><x-pill variant="neutral">{{ __('web.notifications.types.'.($n->data['type'] ?? 'announcement'), [], 'en') }}</x-pill></td>
                                <td data-label="Date">{{ $n->created_at->format('d M Y, h:i A') }}</td>
                                <td data-label="Read">{{ $n->read_at ? $n->read_at->format('d M Y') : 'Unread' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $messages->links() }}</div>
        @endif
    </div>

    <dialog id="announce-dialog" class="admin-modal admin-modal-wide" aria-labelledby="announce-title" @if ($errors->any()) data-open-on-load @endif>
        <form method="POST" action="{{ route('admin.notifications.store') }}" novalidate>
            @csrf
            <div class="admin-modal-head"><h3 id="announce-title">New Announcement</h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>
            <div class="admin-modal-body form-grid">
                <x-admin.translatable-field name="title" label="Title" required idPrefix="an-" hint="Hindi is optional; members see English when it is empty." />
                <x-admin.translatable-field name="message" label="Message" type="textarea" :rows="3" required idPrefix="an-" />
                <div class="form-row">
                    <x-field name="type" type="select" label="Type" :options="Broadcast::TYPES" value="announcement" required />
                    <x-field name="audience" type="select" label="Send to" :options="Broadcast::AUDIENCES" value="all" required />
                </div>
                <x-field name="area_id" type="select" label="Area (when sending to one area)" placeholder="Select area" :options="$areas" />
                <label class="checkbox-row"><input type="checkbox" name="send_sms" value="1" @checked(old('send_sms'))> Also send as SMS (uses SMS credits; keep the message short)</label>
                <p class="muted">Suspended and rejected accounts never receive announcements.</p>
            </div>
            <div class="admin-modal-foot"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Send Announcement</button></div>
        </form>
    </dialog>
</x-layouts.admin>
