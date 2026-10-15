@php use App\Enums\BusinessStatus as BS; $s = $business->status; @endphp
<x-layouts.admin :title="$business->name">
    <div class="admin-page-head">
        <div>
            <h2>{{ $business->name }}</h2>
            <p class="muted"><x-pill :status="$s" /> @if ($business->is_featured)<span class="pill pill-success">Featured</span>@endif · {{ $business->category?->name_en }} · {{ $business->area?->name_en }}</p>
        </div>
        <a href="{{ route('admin.businesses.index', ['status' => $s->value]) }}" class="btn btn-outline">&larr; All Listings</a>
    </div>

    @can('businesses.manage')
        <div class="admin-panel content-section">
            <div class="admin-panel-body row-actions">
                @if (in_array($s, [BS::Pending, BS::ChangesRequested, BS::Rejected, BS::Hidden], true))
                    <form method="POST" action="{{ route('admin.businesses.approve', $business->id) }}" data-confirm="Approve and publish “{{ $business->name }}”?">@csrf @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">{{ $s === BS::Hidden ? 'Unhide (Approve)' : 'Approve & Publish' }}</button></form>
                @endif
                @if (in_array($s, [BS::Pending, BS::Approved], true))
                    <button type="button" class="btn btn-outline btn-sm" data-modal-open="note-dialog" data-form-action="{{ route('admin.businesses.changes', $business->id) }}" data-form-label="Request changes">Request Changes</button>
                @endif
                @if ($s !== BS::Rejected)
                    <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="note-dialog" data-form-action="{{ route('admin.businesses.reject', $business->id) }}" data-form-label="Reject listing">Reject</button>
                @endif
                @if ($s === BS::Approved)
                    <form method="POST" action="{{ route('admin.businesses.feature', $business->id) }}">@csrf @method('PATCH')
                        <button type="submit" class="btn btn-outline btn-sm">{{ $business->is_featured ? 'Unfeature' : 'Feature on Homepage' }}</button></form>
                    <form method="POST" action="{{ route('admin.businesses.hide', $business->id) }}" data-confirm="Hide “{{ $business->name }}” from the directory?">@csrf @method('PATCH')
                        <button type="submit" class="btn btn-outline btn-sm">Hide</button></form>
                    <a href="{{ route('business.show', $business->slug) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">View Live</a>
                @endif
                <a href="{{ route('admin.businesses.edit', $business->id) }}" class="btn btn-outline btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.businesses.destroy', $business->id) }}" data-confirm="Delete “{{ $business->name }}” permanently?">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button></form>
            </div>
        </div>
    @endcan

    @if ($business->admin_note)
        <div class="form-alert form-alert-{{ $s === BS::Rejected ? 'danger' : 'warning' }}" role="status"><strong>Note to owner:</strong> {{ $business->admin_note }}</div>
    @endif

    <div class="grid grid-2 align-start">
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Listing</h3></div>
            <div class="admin-panel-body stack">
                @if ($business->logo)<x-img :image="$business->logo" alt="" class="proof-image" sizes="400px" />@endif
                <div class="message-body">{{ $business->description }}</div>
            </div>
        </div>
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Details</h3></div>
            <div class="admin-panel-body">
                <dl class="detail-list">
                    <div><dt>Owner</dt><dd>@if ($business->member)<a href="{{ route('admin.members.show', $business->member) }}">{{ $business->member->name }}</a> ({{ $business->member->mobile }})@endif</dd></div>
                    <div><dt>Address</dt><dd>{{ $business->address ?? '—' }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $business->phone ?? '—' }} {{ $business->show_phone ? '(public)' : '(hidden)' }}</dd></div>
                    <div><dt>WhatsApp</dt><dd>{{ $business->whatsapp ?? '—' }} {{ $business->show_whatsapp ? '(public)' : '(hidden)' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $business->email ?? '—' }} {{ $business->show_email ? '(public)' : '(hidden)' }}</dd></div>
                    <div><dt>Website</dt><dd class="ref-text">{{ $business->website ?? '—' }}</dd></div>
                    <div><dt>Hours</dt><dd>{{ $business->hours ?? '—' }}</dd></div>
                    <div><dt>Submitted</dt><dd>{{ $business->submitted_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div><dt>Approved</dt><dd>{{ $business->approved_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>

    @can('businesses.manage')
        <dialog id="note-dialog" class="admin-modal" aria-labelledby="note-title" @if ($errors->has('note')) data-open-on-load @endif>
            <form method="POST" action="#">
                @csrf @method('PATCH')
                <div class="admin-modal-head"><h3 id="note-title" data-form-label-target>Note to owner</h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>
                <div class="admin-modal-body form-grid">
                    <p class="muted">The owner sees this note in their member area and gets an SMS.</p>
                    <x-field name="note" type="textarea" :rows="3" label="Note / reason" required maxlength="500" />
                </div>
                <div class="admin-modal-foot"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary">Send</button></div>
            </form>
        </dialog>
    @endcan
</x-layouts.admin>
