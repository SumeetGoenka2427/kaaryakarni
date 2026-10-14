{{-- EN | हिं switch. Real links to the same page in the other language. --}}
<div {{ $attributes->class('lang-toggle') }} role="group" aria-label="{{ __('web.language') }} / Language">
    @foreach (config('portal.locales') as $code => $locale)
        <a href="{{ switch_locale_url($code) }}"
           hreflang="{{ $code }}"
           lang="{{ $code }}"
           @if (app()->getLocale() === $code) aria-current="true" @endif
           title="{{ $locale['native'] }}">{{ $code === 'hi' ? 'हिं' : 'EN' }}</a>
    @endforeach
</div>
