@php use App\Enums\MaritalStatus; use App\Models\MatrimonialProfile; $p = $profile; @endphp
<x-layouts.admin title="Edit Matrimonial Profile">
    <div class="admin-page-head">
        <div><h2>Edit {{ $p->name }}</h2><p class="muted">Admin edits keep the current status (<x-pill :status="$p->status" />) and are recorded in the audit log. Photos and bio data are managed by the member.</p></div>
        <a href="{{ route('admin.matrimonial.show', $p) }}" class="btn btn-outline">&larr; Back</a>
    </div>
    <form method="POST" action="{{ route('admin.matrimonial.update', $p) }}" class="admin-panel settings-panel" novalidate>
        @csrf @method('PUT')
        <div class="admin-panel-body form-grid">
            <div class="form-row-3">
                <x-field name="relation" type="select" label="Profile is for" :options="__('web.matrimonial.relations', [], 'en')" :value="$p->relation" required />
                <x-field name="name" label="Full name" :value="$p->name" required maxlength="120" />
                <x-field name="gender" type="select" label="Gender" :options="['male' => 'Male', 'female' => 'Female']" :value="$p->gender->value" required />
            </div>
            <div class="form-row-3">
                <x-field name="dob" type="date" label="Date of birth" :value="$p->dob->toDateString()" required />
                <x-field name="height_cm" type="select" label="Height" placeholder="—" :options="MatrimonialProfile::heightOptions()" :value="$p->height_cm" />
                <x-field name="marital_status" type="select" label="Marital status" :options="collect(MaritalStatus::cases())->reject(fn ($c) => $c === MaritalStatus::Married)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()" :value="$p->marital_status->value" required />
            </div>
            <div class="form-row-3">
                <x-field name="father_name" label="Father’s name" :value="$p->father_name" maxlength="120" />
                <x-field name="mother_name" label="Mother’s name" :value="$p->mother_name" maxlength="120" />
                <x-field name="gotra" label="Gotra" :value="$p->gotra" maxlength="80" />
            </div>
            <div class="form-row-3">
                <x-field name="native_place" label="Native place" :value="$p->native_place" maxlength="120" />
                <x-field name="area_id" type="select" label="Area" placeholder="—" :options="$areas" :value="$p->area_id" />
                <x-field name="education" label="Education" :value="$p->education" maxlength="150" />
            </div>
            <x-field name="occupation" label="Occupation" :value="$p->occupation" maxlength="150" />
            <x-field name="about" type="textarea" :rows="4" label="About" :value="$p->about" maxlength="1000" />
            <label class="checkbox-row"><input type="hidden" name="show_contact" value="0"><input type="checkbox" name="show_contact" value="1" @checked(old('show_contact', $p->show_contact))> Show the member’s contact number to eligible members</label>
            <div><button type="submit" class="btn btn-primary">Save Profile</button></div>
        </div>
    </form>
</x-layouts.admin>
