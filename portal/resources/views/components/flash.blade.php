{{-- Session flash messages → toasts (JS) with a visible fallback when JS is off. --}}
@foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'status' => 'info'] as $key => $type)
    @if (session()->has($key))
        <div class="container" data-toast="{{ session($key) }}" data-toast-type="{{ $type === 'info' ? '' : $type }}">
            <noscript><div class="form-alert form-alert-{{ $type }}" role="status">{{ session($key) }}</div></noscript>
        </div>
    @endif
@endforeach
