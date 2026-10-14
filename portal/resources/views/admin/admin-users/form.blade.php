@php $editing = $admin->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Admin' : 'Add Admin'">
    <div class="admin-page-head">
        <div>
            <h2>{{ $editing ? 'Edit '.$admin->name : 'Add Admin User' }}</h2>
            <p class="muted">Passwords need at least 10 characters with upper- and lower-case letters and a number.</p>
        </div>
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline">&larr; All Admins</a>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.admin-users.update', $admin) : route('admin.admin-users.store') }}" class="admin-panel settings-panel" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <div class="form-row">
                <x-field name="name" label="Full Name" :value="$admin->name" required maxlength="120" />
                @if ($editing)
                    <div class="field"><label for="f-username-ro">Username</label><input id="f-username-ro" type="text" value="{{ $admin->username }}" disabled><span class="hint">Usernames cannot be changed.</span></div>
                @else
                    <x-field name="username" label="Username" required maxlength="60" autocomplete="off" hint="Letters, numbers, dashes. Used to log in." />
                @endif
            </div>
            <x-field name="email" type="email" label="Email" :value="$admin->email" required maxlength="191" />
            <x-field name="role" type="select" label="Role" :options="$roles" :value="$admin->roles->first()?->name ?? 'content-manager'" required />
            <div class="form-row">
                <x-field name="password" type="password" :label="$editing ? 'New Password (leave empty to keep)' : 'Password'" :required="! $editing" autocomplete="new-password" />
                <x-field name="password_confirmation" type="password" label="Confirm Password" :required="! $editing" autocomplete="new-password" />
            </div>
            @if ($editing)
                <label class="checkbox-row">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $admin->is_active))>
                    <span>Active (can log in)</span>
                </label>
            @endif
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> {{ $editing ? 'Save Changes' : 'Create Admin' }}</button></div>
        </div>
    </form>
</x-layouts.admin>
