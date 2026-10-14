{{-- Navy page header used on inner public pages (design: .page-hero). --}}
@props(['title', 'subtitle' => null, 'breadcrumbs' => []])
<section {{ $attributes->class('page-hero') }}>
    <div class="container">
        @if ($breadcrumbs)
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ localized_route('home') }}">{{ __('web.nav.home') }}</a>
                @foreach ($breadcrumbs as $label => $url)
                    <span aria-hidden="true"> / </span>
                    @if ($url)<a href="{{ $url }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif
                @endforeach
            </nav>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
