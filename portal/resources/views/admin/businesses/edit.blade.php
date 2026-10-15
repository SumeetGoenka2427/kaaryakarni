<x-layouts.admin title="Edit Listing">
    <div class="admin-page-head">
        <div><h2>Edit {{ $business->name }}</h2><p class="muted">Admin edits keep the current status (<x-pill :status="$business->status" />) and are recorded in the audit log.</p></div>
        <a href="{{ route('admin.businesses.show', $business->id) }}" class="btn btn-outline">&larr; Back</a>
    </div>
    <form method="POST" action="{{ route('admin.businesses.update', $business->id) }}" enctype="multipart/form-data" class="admin-panel settings-panel" novalidate>
        @csrf @method('PUT')
        <div class="admin-panel-body form-grid">
            <div class="form-row">
                <x-field name="name" label="Business name" :value="$business->name" required maxlength="150" />
                <x-field name="category_id" type="select" label="Category" :options="$categories" :value="$business->category_id" required />
            </div>
            <x-field name="description" type="textarea" :rows="5" label="Description" :value="$business->description" required maxlength="2000" />
            <div class="form-row">
                <x-field name="area_id" type="select" label="Area" :options="$areas" :value="$business->area_id" required />
                <x-field name="hours" label="Hours" :value="$business->hours" maxlength="150" />
            </div>
            <x-field name="address" type="textarea" :rows="2" label="Address" :value="$business->address" maxlength="500" />
            <div class="form-row-3">
                <x-field name="phone" type="tel" label="Phone" :value="$business->phone" maxlength="10" />
                <x-field name="whatsapp" type="tel" label="WhatsApp" :value="$business->whatsapp" maxlength="10" />
                <x-field name="email" type="email" label="Email" :value="$business->email" maxlength="191" />
            </div>
            <div class="flex flex-wrap gap-4">
                @foreach (['show_phone' => 'Show phone', 'show_whatsapp' => 'Show WhatsApp', 'show_email' => 'Show email'] as $flag => $label)
                    <label class="checkbox-row"><input type="hidden" name="{{ $flag }}" value="0"><input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $business->{$flag}))> {{ $label }}</label>
                @endforeach
            </div>
            <x-field name="website" type="url" label="Website" :value="$business->website" maxlength="255" />
            <x-admin.image-field name="logo" label="Logo / photo" :current="$business->logo" />
            <div><button type="submit" class="btn btn-primary">Save Listing</button></div>
        </div>
    </form>
</x-layouts.admin>
