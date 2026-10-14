{{-- Admin login screen layout (from admin/login.html). --}}
@props(['title' => 'Admin Login'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — Samaj Admin Panel</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @include('partials.head-brand')
</head>
<body class="admin-body">
    <main class="admin-login-shell" id="main">
        <div class="admin-login-card">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
