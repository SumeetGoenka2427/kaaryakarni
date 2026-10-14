{{-- Member profile (web/member/profile.html). No city/state; area from the admin list. --}}
<x-layouts.member :title="__('web.profile.title')">
    <section class="section section-tight">
        <div class="container container-form">
            <h1>{{ __('web.profile.title') }}</h1>
            <p class="muted">{{ __('web.profile.intro') }}</p>

            @if ($errors->any())
                <div class="form-alert form-alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ localized_route('member.profile.update') }}" enctype="multipart/form-data" class="form-grid" data-validate novalidate>
                @csrf
                @method('PUT')

                <div class="profile-photo-row">
                    @if ($member->photo)
                        <img src="{{ route('photos.member', $member) }}?v={{ md5($member->photo['path']) }}" alt="{{ __('web.profile.photo') }}" class="profile-photo" width="96" height="96">
                    @else
                        <span class="profile-photo leader-initials" aria-hidden="true">{{ initials($member->name) }}</span>
                    @endif
                    <div class="profile-photo-input">
                        <x-field name="photo" type="file" :label="__('web.profile.photo')" accept="image/jpeg,image/png,image/webp" :hint="__('web.profile.photo_hint')" />
                        @if ($member->photo)
                            <label class="checkbox-row"><input type="checkbox" name="remove_photo" value="1"> {{ __('web.profile.remove_photo') }}</label>
                        @endif
                    </div>
                </div>

                <div class="form-row">
                    <x-field name="name" :label="__('web.register.full_name')" :value="$member->name" required maxlength="120" autocomplete="name" data-rule="required" />
                    <div class="field">
                        <label for="p-mobile">{{ __('web.auth.mobile') }}</label>
                        <input type="text" id="p-mobile" value="{{ $member->mobile }}" disabled aria-describedby="p-mobile-hint">
                        <span class="hint" id="p-mobile-hint">{{ __('web.profile.mobile_hint') }}</span>
                    </div>
                </div>
                <x-field name="father_or_husband_name" :label="__('web.register.father_name')" :value="$member->father_or_husband_name" maxlength="120" />
                <div class="form-row">
                    <x-field name="dob" type="date" :label="__('web.register.dob')" :value="$member->dob?->toDateString()" :max="now()->subDay()->toDateString()" />
                    <x-field name="gender" type="select" :label="__('web.register.gender')" :placeholder="__('web.register.select')" :options="\App\Enums\Gender::options()" :value="$member->gender?->value" />
                </div>
                <x-field name="email" type="email" :label="__('web.family.email')" :value="$member->email" placeholder="you@example.com" maxlength="191" data-rule="email" />
                <x-field name="area_id" type="select" :label="__('web.register.area')" :placeholder="__('web.register.area_placeholder')" :options="$areas" :value="$member->area_id" required />
                <x-field name="address" type="textarea" :rows="2" :label="__('web.register.address')" :value="$member->address" maxlength="500" />

                <div class="form-row">
                    <div class="field">
                        <span class="field-label">{{ __('web.profile.verification_status') }}</span>
                        <div><x-pill :status="$member->status" /></div>
                    </div>
                    <div class="field">
                        <span class="field-label">{{ __('web.profile.membership_no') }}</span>
                        <div>{{ $member->membership_no ?? '—' }}</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __('web.profile.save') }}</button>
            </form>
        </div>
    </section>
</x-layouts.member>
