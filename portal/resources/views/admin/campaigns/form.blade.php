@php $editing = $campaign->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Campaign' : 'Add Campaign'">
    <div class="admin-page-head">
        <div>
            <h2>{{ $editing ? 'Edit '.$campaign->title_en : 'Add Donation Campaign' }}</h2>
            @if ($campaign->is_permanent)<p class="muted">This is the permanent Samaj Donation fund. It is always open and cannot be deleted or deactivated.</p>@endif
        </div>
        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline">&larr; All Campaigns</a>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate class="admin-panel settings-panel"
          action="{{ $editing ? route('admin.campaigns.update', $campaign->id) : route('admin.campaigns.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <x-admin.translatable-field name="title" label="Campaign name" :model="$campaign" required />
            <x-admin.translatable-field name="description" label="Description" type="textarea" :rows="4" :model="$campaign" hint="Leave an empty line between paragraphs." />
            <div class="form-row">
                <x-field name="target_amount" type="number" label="Target amount (₹)" :value="$campaign->target_amount ? (int) $campaign->target_amount : null" min="1" step="1" hint="Optional. Without a target no progress bar is shown." />
                <x-field name="slug" label="Web address" :value="$campaign->slug" :placeholder="'e.g. diwali-utsav'" maxlength="120" :hint="'/donations/'.($campaign->slug ?: '…').($campaign->is_permanent ? ' (fixed)' : '')" :disabled="$campaign->is_permanent" />
            </div>
            @unless ($campaign->is_permanent)
                <div class="form-row-3">
                    <x-field name="starts_at" type="date" label="Opens on (optional)" :value="$campaign->starts_at?->toDateString()" />
                    <x-field name="ends_at" type="date" label="Closes after (optional)" :value="$campaign->ends_at?->toDateString()" />
                    <x-field name="sort_order" type="number" label="Display order" :value="$campaign->sort_order" min="0" max="999" />
                </div>
            @else
                <input type="hidden" name="sort_order" value="{{ $campaign->sort_order }}">
            @endunless
            <x-admin.image-field :current="$campaign->image" hint="Landscape photo shown on campaign cards and pages." />

            <fieldset class="panel-fieldset">
                <legend>Payment options</legend>
                <label class="checkbox-row"><input type="checkbox" name="allow_online" value="1" @checked(old('allow_online', $campaign->allow_online))> Accept online payments (Razorpay)</label>
                <label class="checkbox-row"><input type="checkbox" name="allow_offline" value="1" @checked(old('allow_offline', $campaign->allow_offline))> Accept offline payments (verified by the office)</label>
            </fieldset>

            <fieldset class="panel-fieldset">
                <legend>Event tickets (optional)</legend>
                <label class="checkbox-row"><input type="checkbox" name="has_tickets" value="1" @checked(old('has_tickets', $campaign->has_tickets))> Sell fixed-price tickets / passes for this campaign</label>
                <x-admin.translatable-field name="ticket_label" label="Ticket name" :model="$campaign" hint="e.g. Diwali Utsav Entry Pass" />
                <x-field name="ticket_price" type="number" label="Ticket price (₹)" :value="$campaign->ticket_price ? (int) $campaign->ticket_price : null" min="1" step="1" />
            </fieldset>

            @unless ($campaign->is_permanent)
                <label class="checkbox-row"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $campaign->exists ? $campaign->is_active : true))> Active (shown on the website)</label>
            @endunless
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save Campaign</button></div>
        </div>
    </form>
</x-layouts.admin>
