<x-layouts.admin title="Admin Users">
    <div class="admin-page-head">
        <div>
            <h2>Admin Users</h2>
            <p class="muted">People who can sign in to this admin panel, and what each of them is allowed to manage.</p>
        </div>
        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">+ Add Admin</a>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field search-field">
            <label for="q">Search</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Name, username or email">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            @if ($search !== '')<a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
        </div>
    </form>

    @if ($admins->isEmpty())
        <x-empty-state title="No admin users found" icon="search" />
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th scope="col">Name</th><th scope="col">Username</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Last Login</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($admins as $a)
                        <tr>
                            <td data-label="Name"><strong>{{ $a->name }}</strong><br><span class="muted">{{ $a->email }}</span></td>
                            <td data-label="Username">{{ $a->username }}</td>
                            <td data-label="Role">{{ $roles[$a->roles->first()?->name] ?? '—' }}</td>
                            <td data-label="Status"><x-pill :variant="$a->is_active ? 'success' : 'neutral'">{{ $a->is_active ? 'Active' : 'Inactive' }}</x-pill></td>
                            <td data-label="Last Login">{{ $a->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</td>
                            <td data-label="Actions"><a href="{{ route('admin.admin-users.edit', $a) }}" class="btn btn-outline btn-sm">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $admins->links() }}</div>
    @endif
</x-layouts.admin>
