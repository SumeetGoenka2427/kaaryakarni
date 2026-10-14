<x-layouts.admin-guest title="Admin Login">
    <div class="text-center login-head">
        @php $logo = app(\App\Services\Branding\BrandingService::class)->get('logo_header'); @endphp
        @if ($logo)
            <img src="{{ $logo->url() }}" width="{{ $logo->width }}" height="{{ $logo->height }}" alt="{{ setting('org_name_en') }}" class="login-logo">
        @else
            <span class="brand-mark login-mark" aria-hidden="true">KS</span>
        @endif
        <h1>Samaj Admin Panel</h1>
        <p class="muted">{{ setting('org_name_en') }}</p>
    </div>

    @foreach (['status' => 'info', 'warning' => 'warning'] as $key => $type)
        @if (session($key))
            <div class="form-alert form-alert-{{ $type }}" role="status">{{ session($key) }}</div>
        @endif
    @endforeach

    @error('username')
        <div class="form-alert form-alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('admin.login.store') }}" class="form-grid" novalidate>
        @csrf
        <div class="field">
            <label for="au-username">Username</label>
            <input type="text" id="au-username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus maxlength="60">
        </div>
        <div class="field">
            <label for="au-password">Password</label>
            <input type="password" id="au-password" name="password" autocomplete="current-password" required maxlength="200">
        </div>
        <button type="submit" class="btn btn-primary btn-block">
            <span class="spinner" aria-hidden="true"></span> Login to Admin Panel
        </button>
    </form>

    <p class="text-center muted login-back"><a href="{{ route('home') }}">&larr; Back to Public Site</a></p>
</x-layouts.admin-guest>
