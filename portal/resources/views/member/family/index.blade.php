{{-- Member → My Family. Private: only the member and admin see this. --}}
<x-layouts.member :title="__('web.family.title')">
    <section class="section section-tight">
        <div class="container">
            <div class="page-title-row">
                <div>
                    <h1>{{ __('web.family.title') }}</h1>
                    <p class="muted">{{ __('web.family.intro') }}</p>
                </div>
                <a href="{{ localized_route('member.family.create') }}" class="btn btn-primary">+ {{ __('web.family.add') }}</a>
            </div>

            @if ($family->isEmpty())
                <x-empty-state :title="__('web.family.empty')">
                    <p>{{ __('web.family.empty_text') }}</p>
                    <a href="{{ localized_route('member.family.create') }}" class="btn btn-primary">{{ __('web.family.add') }}</a>
                </x-empty-state>
            @else
                <div class="grid grid-3">
                    @foreach ($family as $person)
                        <article class="card family-card">
                            <div class="card-body">
                                <div class="family-card-head">
                                    @if ($person->photo)
                                        <img src="{{ route('photos.family', $person) }}?v={{ md5($person->photo['path']) }}" alt="" class="family-photo" width="56" height="56" loading="lazy">
                                    @else
                                        <span class="family-photo leader-initials" aria-hidden="true">{{ initials($person->name) }}</span>
                                    @endif
                                    <div>
                                        <span class="panel-label">{{ $person->relation->label() }}</span>
                                        <h2 class="family-name">@if ($person->is_deceased){{ __('web.family.late') }} @endif{{ $person->name }}</h2>
                                    </div>
                                </div>
                                <dl class="family-facts">
                                    @if ($person->age())<div><dt>{{ __('web.family.dob') }}</dt><dd>{{ $person->dob->translatedFormat('d M Y') }} ({{ __('web.family.age', ['age' => $person->age()]) }})</dd></div>@endif
                                    @if ($person->marital_status)<div><dt>{{ __('web.family.marital_status') }}</dt><dd>{{ $person->marital_status->label() }}</dd></div>@endif
                                    @if ($person->occupation)<div><dt>{{ __('web.family.occupation') }}</dt><dd>{{ $person->occupation }}</dd></div>@endif
                                    @if ($person->education)<div><dt>{{ __('web.family.education') }}</dt><dd>{{ $person->education }}</dd></div>@endif
                                    @if ($person->mobile)<div><dt>{{ __('web.family.mobile') }}</dt><dd>{{ $person->mobile }}</dd></div>@endif
                                    @if ($person->blood_group)<div><dt>{{ __('web.family.blood_group') }}</dt><dd>{{ $person->blood_group }}</dd></div>@endif
                                </dl>
                                <div class="row-actions family-actions">
                                    <a href="{{ localized_route('member.family.edit', ['family' => $person->ulid]) }}" class="btn btn-outline btn-sm">{{ __('web.common.edit') }}</a>
                                    <form method="POST" action="{{ localized_route('member.family.destroy', ['family' => $person->ulid]) }}" data-confirm="{{ __('web.family.delete_confirm', ['name' => $person->name]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm">{{ __('web.common.delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
            <p class="muted privacy-note">{{ __('web.family.private_note') }}</p>
        </div>
    </section>
</x-layouts.member>
