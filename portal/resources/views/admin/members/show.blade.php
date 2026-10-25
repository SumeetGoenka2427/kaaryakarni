@php use App\Enums\MemberStatus; $s = $member->status; $tab = request('tab', 'profile'); @endphp
<x-layouts.admin :title="$member->name ?? 'Member'">
    <div class="admin-page-head">
        <div class="flex gap-4 align-center">
            @if ($member->photo)
                <img src="{{ route('photos.member', $member) }}?v={{ md5($member->photo['path']) }}" alt="" class="profile-photo" width="72" height="72">
            @endif
            <div>
                <h2>{{ $member->name ?? '(profile incomplete)' }}</h2>
                <p class="muted">{{ $member->membership_no ?? 'No membership ID yet' }} · {{ $member->mobile }} · <x-pill :status="$s" /></p>
            </div>
        </div>
        <a href="{{ route('admin.members.index') }}" class="btn btn-outline">&larr; All Members</a>
    </div>

    @can('members.manage')
        <div class="admin-panel content-section">
            <div class="admin-panel-body page-actions">
                <p class="page-actions-status">
                    @switch($s)
                        @case(MemberStatus::UnderVerification) <strong>Waiting for verification.</strong> Check the details below, then verify or reject the profile. @break
                        @case(MemberStatus::Pending) <strong>Registration not finished.</strong> The member has not submitted the full profile yet. @break
                        @case(MemberStatus::Verified) <strong>Verified member.</strong> @break
                        @case(MemberStatus::Rejected) <strong>Rejected.</strong> You can verify the profile after the member corrects it. @break
                        @case(MemberStatus::Suspended) <strong>Suspended.</strong> The member cannot log in. @break
                    @endswitch
                </p>
                <div class="page-actions-buttons">
                    @if (in_array($s, [MemberStatus::UnderVerification, MemberStatus::Rejected, MemberStatus::Pending], true))
                        <form method="POST" action="{{ route('admin.members.verify', $member) }}" data-confirm="Verify {{ $member->name }}'s profile?">@csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary">Verify Profile</button></form>
                    @endif
                    @if ($s === MemberStatus::Suspended)
                        <form method="POST" action="{{ route('admin.members.reinstate', $member) }}" data-confirm="Reinstate {{ $member->name }}?">@csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary">Reinstate Member</button></form>
                    @endif
                    @if ($s === MemberStatus::Verified)
                        <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-outline">Edit Details</a>
                    @endif
                    <x-admin.actions label="More actions">
                        @unless ($s === MemberStatus::Verified)
                            <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-outline btn-sm">Edit details</a>
                        @endunless
                        @if (in_array($s, [MemberStatus::UnderVerification, MemberStatus::Pending, MemberStatus::Verified], true))
                            <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="reject-dialog">Reject profile</button>
                        @endif
                        @unless ($s === MemberStatus::Suspended)
                            <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="suspend-dialog">Suspend member</button>
                        @endunless
                    </x-admin.actions>
                </div>
            </div>
        </div>
    @endcan

    @if ($member->rejection_reason && $s === MemberStatus::Rejected)
        <div class="form-alert form-alert-danger" role="status"><strong>Rejection reason:</strong> {{ $member->rejection_reason }}</div>
    @endif

    <nav class="tabs" aria-label="Member sections">
        @foreach (['profile' => 'Profile', 'family' => 'Family ('.$member->family->count().')', 'history' => 'Verification History'] as $key => $label)
            <a href="{{ route('admin.members.show', [$member, 'tab' => $key]) }}" @class(['tab-btn', 'is-active' => $tab === $key])>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($tab === 'family')
        <div class="admin-panel">
            <div class="admin-panel-head">
                <h3>Family Members</h3>
                @can('members.manage')<a href="{{ route('admin.members.family.create', $member) }}" class="btn btn-primary btn-sm">+ Add</a>@endcan
            </div>
            @if ($member->family->isEmpty())
                <div class="admin-panel-body"><p class="muted">No family members added.</p></div>
            @else
                <div class="data-table-wrap borderless">
                    <table class="data-table">
                        <thead><tr><th scope="col">Name</th><th scope="col">Relation</th><th scope="col">DOB / Age</th><th scope="col">Marital</th><th scope="col">Occupation</th><th scope="col">Mobile</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($member->family as $p)
                                <tr>
                                    <td data-label="Name"><strong>@if ($p->is_deceased)Late @endif{{ $p->name }}</strong></td>
                                    <td data-label="Relation">{{ $p->relation->label() }}</td>
                                    <td data-label="DOB / Age">{{ $p->dob?->format('d M Y') ?? '—' }}@if ($p->age()) ({{ $p->age() }})@endif</td>
                                    <td data-label="Marital">{{ $p->marital_status?->label() ?? '—' }}</td>
                                    <td data-label="Occupation">{{ $p->occupation ?? '—' }}</td>
                                    <td data-label="Mobile">{{ $p->mobile ?? '—' }}</td>
                                    <td data-label="Actions">
                                        @can('members.manage')
                                            <x-admin.actions>
                                                <a href="{{ route('admin.members.family.edit', [$member, $p->ulid]) }}" class="btn btn-outline btn-sm">Edit</a>
                                                <form method="POST" action="{{ route('admin.members.family.destroy', [$member, $p->ulid]) }}" data-confirm="Remove {{ $p->name }}?">@csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger-outline btn-sm">Remove</button></form>
                                            </x-admin.actions>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @elseif ($tab === 'history')
        <div class="admin-panel">
            <div class="admin-panel-body stack">
                @forelse ($member->verifications as $v)
                    <div class="audit-row">
                        <strong>{{ ucfirst($v->action) }}</strong>
                        <span class="muted">{{ $v->admin?->name ?? ($v->action === 'submitted' ? 'Member' : 'System') }} · {{ $v->created_at->format('d M Y, h:i A') }}</span>
                        @if ($v->reason)<span>{{ $v->reason }}</span>@endif
                    </div>
                @empty
                    <p class="muted">No history yet.</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="admin-panel">
            <div class="admin-panel-body">
                <dl class="detail-list">
                    <div><dt>Full name</dt><dd>{{ $member->name ?? '—' }}</dd></div>
                    <div><dt>Father / Husband</dt><dd>{{ $member->father_or_husband_name ?? '—' }}</dd></div>
                    <div><dt>Mobile</dt><dd>{{ $member->mobile }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $member->email ?? '—' }}</dd></div>
                    <div><dt>Gender</dt><dd>{{ $member->gender?->label() ?? '—' }}</dd></div>
                    <div><dt>Date of birth</dt><dd>{{ $member->dob?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt>Area / Locality</dt><dd>{{ $member->area?->name_en ?? '—' }}</dd></div>
                    <div><dt>Address</dt><dd>{{ $member->address ?? '—' }}</dd></div>
                    <div><dt>Preferred language</dt><dd>{{ $member->locale === 'hi' ? 'हिन्दी' : 'English' }}</dd></div>
                    <div><dt>Registered</dt><dd>{{ $member->created_at->format('d M Y') }}</dd></div>
                    <div><dt>Verified</dt><dd>{{ $member->verified_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt>Last login</dt><dd>{{ $member->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</dd></div>
                </dl>
            </div>
        </div>
    @endif

    @can('members.manage')
        @foreach (['reject' => ['Reject Profile', 'The member will see this reason and receive an SMS.', 'Reject'], 'suspend' => ['Suspend Member', 'The member will be signed out and cannot log in until reinstated.', 'Suspend']] as $action => [$title, $help, $button])
            <dialog id="{{ $action }}-dialog" class="admin-modal" aria-labelledby="{{ $action }}-title" @if ($errors->has('reason') && old('_action') === $action) data-open-on-load @endif>
                <form method="POST" action="{{ route('admin.members.'.$action, $member) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="_action" value="{{ $action }}">
                    <div class="admin-modal-head"><h3 id="{{ $action }}-title">{{ $title }}</h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>
                    <div class="admin-modal-body form-grid">
                        <p class="muted">{{ $help }}</p>
                        <x-field name="reason" :id="$action.'-reason'" type="textarea" :rows="3" label="Reason" required maxlength="500" />
                    </div>
                    <div class="admin-modal-foot">
                        <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-danger-outline">{{ $button }}</button>
                    </div>
                </form>
            </dialog>
        @endforeach
    @endcan
</x-layouts.admin>
