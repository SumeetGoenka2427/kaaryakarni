@php $editing = $member->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Member' : 'Register Member'">
    <div class="admin-page-head">
        <div>
            <h2>{{ $editing ? 'Edit '.$member->name : 'Register a Member' }}</h2>
            <p class="muted">{{ $editing ? 'Changes are recorded in the audit log.' : 'For offline / paper registrations. The member can log in with this mobile number using OTP. The profile starts “Under Verification”.' }}</p>
        </div>
        <a href="{{ $editing ? route('admin.members.show', $member) : route('admin.members.index') }}" class="btn btn-outline">&larr; Back</a>
    </div>
    <form method="POST" enctype="multipart/form-data" class="admin-panel settings-panel" novalidate
          action="{{ $editing ? route('admin.members.update', $member) : route('admin.members.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <div class="form-row">
                <x-field name="name" label="Full name" :value="$member->name" required maxlength="120" />
                <x-field name="father_or_husband_name" label="Father’s / Husband’s name" :value="$member->father_or_husband_name" maxlength="120" />
            </div>
            <div class="form-row">
                <x-field name="mobile" type="tel" label="Mobile number" :value="$member->mobile" required maxlength="10" inputmode="numeric" hint="Used for OTP login. Must be unique." />
                <x-field name="email" type="email" label="Email" :value="$member->email" maxlength="191" />
            </div>
            <div class="form-row">
                <x-field name="dob" type="date" label="Date of birth" :value="$member->dob?->toDateString()" />
                <x-field name="gender" type="select" label="Gender" placeholder="Select" :options="\App\Enums\Gender::options()" :value="$member->gender?->value" />
            </div>
            <x-field name="area_id" type="select" label="Area / Locality" placeholder="Select area" :options="$areas" :value="$member->area_id" required />
            <x-field name="address" type="textarea" :rows="2" label="Address" :value="$member->address" maxlength="500" />
            <x-field name="photo" type="file" label="Photo" accept="image/jpeg,image/png,image/webp" hint="Private: visible only to the member and admins." />
            @if ($member->photo)<label class="checkbox-row"><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label>@endif
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> {{ $editing ? 'Save Changes' : 'Register Member' }}</button></div>
        </div>
    </form>
</x-layouts.admin>
