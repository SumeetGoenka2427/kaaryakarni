<x-layouts.admin title="Settings">
    @php $m = (object) $s; @endphp

    <div class="admin-page-head">
        <div>
            <h2>Samaj Portal Settings</h2>
            <p class="muted">Organisation identity, office contact details, the one-time lifetime membership fee and privacy preferences used across the public website. Bilingual fields have English and हिन्दी tabs.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="form-alert form-alert-danger" role="alert">Please correct the highlighted fields ({{ $errors->count() }}).</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form">
        @csrf
        @method('PUT')

        <section class="admin-panel settings-panel" aria-labelledby="sec-org">
            <div class="admin-panel-head"><h3 id="sec-org">Organisation</h3></div>
            <div class="admin-panel-body form-grid">
                <x-admin.translatable-field name="org_name" label="Organisation Name" :model="$m" required hint="e.g. Khandelwal Samaj, Jaipur. Used in page titles, footer and receipts." />
                <x-admin.translatable-field name="org_short_name" label="Short Name (beside logo)" :model="$m" required />
                <x-admin.translatable-field name="tagline" label="Tagline (footer)" type="textarea" :rows="2" :model="$m" />
                <x-field name="founded_year" type="number" label="Year Founded" :value="$s['founded_year']" hint="Optional. Used for the “Years of Service” statistic." min="1800" :max="now()->year" />
            </div>
        </section>

        <section class="admin-panel settings-panel" aria-labelledby="sec-contact">
            <div class="admin-panel-head"><h3 id="sec-contact">Samaj Office &amp; Contact</h3></div>
            <div class="admin-panel-body form-grid">
                <x-admin.translatable-field name="office_name" label="Office Name (top bar)" :model="$m" />
                <x-admin.translatable-field name="address" label="Full Address" type="textarea" :rows="2" :model="$m" />
                <div class="form-row">
                    <x-field name="phone_primary" type="tel" label="Primary Phone" :value="$s['phone_primary']" />
                    <x-field name="phone_secondary" type="tel" label="Second Phone" :value="$s['phone_secondary']" />
                </div>
                <div class="form-row">
                    <x-field name="email" type="email" label="Office Email" :value="$s['email']" />
                    <x-field name="whatsapp" type="tel" label="WhatsApp Number" :value="$s['whatsapp']" hint="10-digit mobile number" inputmode="numeric" maxlength="10" />
                </div>
                <x-admin.translatable-field name="office_hours" label="Office Hours" :model="$m" />
                <x-field name="map_url" type="url" label="Google Maps Link" :value="$s['map_url']" hint="https:// link to the Samaj Bhawan location" />
            </div>
        </section>

        <section class="admin-panel settings-panel" aria-labelledby="sec-social">
            <div class="admin-panel-head"><h3 id="sec-social">Social Media</h3></div>
            <div class="admin-panel-body form-grid">
                <x-field name="social_facebook" type="url" label="Facebook Page" :value="$s['social_facebook']" />
                <x-field name="social_instagram" type="url" label="Instagram" :value="$s['social_instagram']" />
                <x-field name="social_youtube" type="url" label="YouTube Channel" :value="$s['social_youtube']" />
            </div>
        </section>

        <section class="admin-panel settings-panel" aria-labelledby="sec-membership">
            <div class="admin-panel-head"><h3 id="sec-membership">Membership &amp; Privacy</h3></div>
            <div class="admin-panel-body form-grid">
                <x-field name="membership_fee" type="number" label="Lifetime Membership Fee (₹)" :value="$s['membership_fee']" required min="0" step="1"
                         hint="One-time fee. Membership never expires and has no renewal." />
                @foreach ([
                    'donor_names_public' => 'Show donor names publicly on the homepage and donations pages (donors can still choose to stay anonymous)',
                    'business_reapproval_on_edit' => 'Send a business listing back for approval when its name, category, description or logo is edited',
                    'show_name_beside_logo' => 'Show the Samaj name beside the uploaded logo in the header and footer',
                ] as $flag => $text)
                    <label class="checkbox-row">
                        <input type="hidden" name="{{ $flag }}" value="0">
                        <input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $s[$flag]))>
                        <span>{{ $text }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="admin-panel settings-panel" aria-labelledby="sec-theme">
            <div class="admin-panel-head"><h3 id="sec-theme">Theme Colours</h3></div>
            <div class="admin-panel-body form-grid">
                <p class="muted">Optional. Leave empty to keep the standard navy / gold / saffron design colours.</p>
                <div class="form-row-3">
                    @foreach (['color_primary' => 'Primary (navy)', 'color_gold' => 'Gold', 'color_accent' => 'Accent (saffron)'] as $key => $label)
                        <x-field :name="$key" :label="$label" :value="$s[$key]" placeholder="#0e2a52" pattern="#[0-9a-fA-F]{6}" maxlength="7" hint="Hex colour, e.g. #0e2a52" />
                    @endforeach
                </div>
            </div>
        </section>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save Settings</button>
        </div>
    </form>
</x-layouts.admin>
