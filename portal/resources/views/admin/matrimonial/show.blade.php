@php use App\Enums\MatrimonialStatus as MS; $s = $profile->status; @endphp
<x-layouts.admin :title="$profile->name">
    <div class="admin-page-head">
        <div>
            <h2>{{ $profile->name }}</h2>
            <p class="muted"><x-pill :status="$s" /> · {{ ucfirst($profile->relation) }} of {{ $profile->member?->name ?? '—' }} · {{ $profile->age() }} yrs · {{ $profile->gender->label() }}</p>
        </div>
        <a href="{{ route('admin.matrimonial.index', ['status' => $s->value]) }}" class="btn btn-outline">&larr; All Profiles</a>
    </div>

    @can('matrimonial.manage')
        <div class="admin-panel content-section">
            <div class="admin-panel-body page-actions">
                <p class="page-actions-status">
                    @switch($s)
                        @case(MS::Pending) <strong>Waiting for your review.</strong> Approve to show it to eligible Lifetime Members. @break
                        @case(MS::Approved) <strong>Approved</strong> — visible to eligible logged-in Lifetime Members only. @break
                        @case(MS::Rejected) <strong>Rejected.</strong> Not visible to members. @break
                        @case(MS::Hidden) <strong>Hidden.</strong> Not visible to members until approved again. @break
                        @case(MS::Suspended) <strong>Suspended.</strong> Not visible to members. @break
                        @default Not visible to members.
                    @endswitch
                </p>
                <div class="page-actions-buttons">
                    @if (in_array($s, [MS::Pending, MS::Rejected, MS::Hidden, MS::Suspended], true))
                        <form method="POST" action="{{ route('admin.matrimonial.approve', $profile) }}" data-confirm="Approve “{{ $profile->name }}”? Eligible members will be able to see this profile.">@csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary">{{ $s === MS::Pending ? 'Approve Profile' : 'Approve & Show Again' }}</button></form>
                    @endif
                    @if ($s === MS::Pending)
                        <button type="button" class="btn btn-danger-outline" data-modal-open="note-dialog" data-form-action="{{ route('admin.matrimonial.reject', $profile) }}" data-form-label="Reject profile">Reject</button>
                    @endif
                    <x-admin.actions label="More actions">
                        <a href="{{ route('admin.matrimonial.edit', $profile) }}" class="btn btn-outline btn-sm">Edit profile details</a>
                        @if ($s === MS::Approved)
                            <form method="POST" action="{{ route('admin.matrimonial.hide', $profile) }}" data-confirm="Hide “{{ $profile->name }}” from members?">@csrf @method('PATCH')
                                <button type="submit" class="btn btn-outline btn-sm">Hide from members</button></form>
                        @endif
                        @if (in_array($s, [MS::Approved, MS::Hidden], true))
                            <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="note-dialog" data-form-action="{{ route('admin.matrimonial.reject', $profile) }}" data-form-label="Reject profile">Reject</button>
                        @endif
                        @if (in_array($s, [MS::Pending, MS::Approved, MS::Hidden], true))
                            <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="note-dialog" data-form-action="{{ route('admin.matrimonial.suspend', $profile) }}" data-form-label="Suspend profile">Suspend</button>
                        @endif
                        <form method="POST" action="{{ route('admin.matrimonial.destroy', $profile) }}" data-confirm="Delete “{{ $profile->name }}” permanently? Photos and bio data will be removed.">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger-outline btn-sm">Delete permanently</button></form>
                    </x-admin.actions>
                </div>
            </div>
        </div>
    @endcan

    @if ($profile->admin_note)
        <div class="form-alert form-alert-danger" role="status"><strong>Note to owner:</strong> {{ $profile->admin_note }}</div>
    @endif

    <div class="grid grid-2 align-start">
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Photos &amp; Bio Data</h3></div>
            <div class="admin-panel-body stack">
                @if ($profile->photos->isNotEmpty())
                    <div class="bio-photo-grid">
                        @foreach ($profile->photos as $photo)
                            <a href="{{ route('matrimonial.photo', [$photo->id, 800]) }}" target="_blank" rel="noopener"><img src="{{ route('matrimonial.photo', [$photo->id, 320]) }}" alt="Photo of {{ $profile->name }}" width="160" height="200" loading="lazy" class="bio-photo-img"></a>
                        @endforeach
                    </div>
                @else
                    <p class="muted">No photos.</p>
                @endif
                @if ($profile->bio_data)
                    <a href="{{ route('matrimonial.biodata', $profile) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Open Bio Data PDF ({{ number_format(($profile->bio_data['size'] ?? 0) / 1024) }} KB)</a>
                @endif
                @if ($profile->about)<div class="message-body">{{ $profile->about }}</div>@endif
            </div>
        </div>
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Details</h3></div>
            <div class="admin-panel-body">
                <dl class="detail-list">
                    <div><dt>Registered by</dt><dd>@if ($profile->member)<a href="{{ route('admin.members.show', $profile->member) }}">{{ $profile->member->name }}</a> ({{ $profile->member->mobile }})@endif</dd></div>
                    <div><dt>Relation</dt><dd>{{ ucfirst($profile->relation) }}</dd></div>
                    <div><dt>Date of birth</dt><dd>{{ $profile->dob->format('d M Y') }} ({{ $profile->age() }} yrs)</dd></div>
                    <div><dt>Height</dt><dd>{{ $profile->heightLabel() ?? '—' }}</dd></div>
                    <div><dt>Marital status</dt><dd>{{ $profile->marital_status->label() }}</dd></div>
                    <div><dt>Father / Mother</dt><dd>{{ $profile->father_name ?? '—' }} / {{ $profile->mother_name ?? '—' }}</dd></div>
                    <div><dt>Gotra</dt><dd>{{ $profile->gotra ?? '—' }}</dd></div>
                    <div><dt>Native place</dt><dd>{{ $profile->native_place ?? '—' }}</dd></div>
                    <div><dt>Area</dt><dd>{{ $profile->area?->name_en ?? '—' }}</dd></div>
                    <div><dt>Education</dt><dd>{{ $profile->education ?? '—' }}</dd></div>
                    <div><dt>Occupation</dt><dd>{{ $profile->occupation ?? '—' }}</dd></div>
                    <div><dt>Contact shown</dt><dd>{{ $profile->show_contact ? 'Yes — to eligible members' : 'No' }}</dd></div>
                    <div><dt>Submitted</dt><dd>{{ $profile->submitted_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div><dt>Approved</dt><dd>{{ $profile->approved_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>

    @if ($profile->reports->isNotEmpty())
        <div class="admin-panel content-section">
            <div class="admin-panel-head"><h3>Member Reports</h3></div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th scope="col">Reason</th><th scope="col">Details</th><th scope="col">By</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($profile->reports as $r)
                            <tr>
                                <td data-label="Reason">{{ __('web.matrimonial.report_reasons.'.$r->reason, [], 'en') }}</td>
                                <td data-label="Details">{{ $r->details ?: '—' }}</td>
                                <td data-label="By">{{ $r->reporter?->name ?? '—' }}</td>
                                <td data-label="Date">{{ $r->created_at->format('d M Y') }}</td>
                                <td data-label="Status"><x-pill :variant="$r->status === 'open' ? 'warning' : 'success'">{{ ucfirst($r->status) }}</x-pill></td>
                                <td data-label="Actions">@if ($r->status === 'open')@can('matrimonial.manage')<form method="POST" action="{{ route('admin.matrimonial.reports.resolve', $r) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-outline btn-sm">Mark Resolved</button></form>@endcan @endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @can('matrimonial.manage')
        <dialog id="note-dialog" class="admin-modal" aria-labelledby="note-title" @if ($errors->has('note')) data-open-on-load @endif>
            <form method="POST" action="#">
                @csrf @method('PATCH')
                <div class="admin-modal-head"><h3 id="note-title" data-form-label-target>Reason</h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>
                <div class="admin-modal-body form-grid">
                    <p class="muted">The member sees this reason in their member area.</p>
                    <x-field name="note" type="textarea" :rows="3" label="Reason" required maxlength="500" />
                </div>
                <div class="admin-modal-foot"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary">Confirm</button></div>
            </form>
        </dialog>
    @endcan
</x-layouts.admin>
