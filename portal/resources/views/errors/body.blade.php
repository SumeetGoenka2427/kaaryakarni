<x-empty-state :title="__('web.errors.'.$code.'_title')" :icon="$code === 403 ? 'lock' : ($code === 404 ? 'search' : 'alert')">
    <p>{{ __('web.errors.'.$code.'_text') }}</p>
    <p class="error-code">{{ __('web.errors.code') }}: {{ $code }}</p>
    <div class="error-actions">
        <a href="{{ localized_route('home') }}" class="btn btn-primary">{{ __('web.errors.go_home') }}</a>
    </div>
</x-empty-state>
