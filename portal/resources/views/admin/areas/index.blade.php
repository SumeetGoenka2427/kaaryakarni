<x-layouts.admin title="Jaipur Areas">
    <div class="admin-page-head">
        <div>
            <h2>Jaipur Area Management</h2>
            <p class="muted">These areas / localities appear in the member registration and profile forms. Use the arrows to set the dropdown order. Areas in use can be deactivated (hidden from forms) but not deleted.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.areas.store') }}" class="admin-panel content-section" novalidate>
        @csrf
        <div class="admin-panel-head"><h3>Add Area</h3></div>
        <div class="admin-panel-body form-row-3 align-end">
            <x-field name="name_en" label="Area name (English)" required maxlength="100" placeholder="e.g. Malviya Nagar" />
            <x-field name="name_hi" label="Area name (हिन्दी)" maxlength="100" placeholder="जैसे मालवीय नगर" lang="hi" />
            <div><button type="submit" class="btn btn-primary">+ Add Area</button></div>
        </div>
    </form>

    @if ($areas->where('is_sample', true)->isNotEmpty())
        <div class="form-alert form-alert-warning" role="status">{{ $areas->where('is_sample', true)->count() }} area(s) are <strong>sample data</strong>. Replace them with the Samaj's final area list — editing an area marks it as real.</div>
    @endif

    @if ($areas->isEmpty())
        <x-empty-state title="No areas yet">
            <p>Add the first Jaipur area above. Registration needs at least one active area.</p>
        </x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Order</th><th scope="col">Area</th><th scope="col">Members</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($areas as $i => $area)
                        <tr>
                            <td data-label="Order">
                                <div class="order-controls">
                                    <form method="POST" action="{{ route('admin.areas.move', $area) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="up">
                                        <button type="submit" class="btn btn-outline btn-sm btn-icon" aria-label="Move {{ $area->name_en }} up" @disabled($i === 0)>&uarr;</button></form>
                                    <form method="POST" action="{{ route('admin.areas.move', $area) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="down">
                                        <button type="submit" class="btn btn-outline btn-sm btn-icon" aria-label="Move {{ $area->name_en }} down" @disabled($i === $areas->count() - 1)>&darr;</button></form>
                                </div>
                            </td>
                            <td data-label="Area">
                                <strong>{{ $area->name_en }}</strong>
                                @if ($area->name_hi)<span class="muted" lang="hi"> · {{ $area->name_hi }}</span>@else<span class="pill pill-danger">HI missing</span>@endif
                                @if ($area->is_sample)<span class="sample-badge">Sample</span>@endif
                            </td>
                            <td data-label="Members">{{ $area->members_count }}</td>
                            <td data-label="Status"><x-pill :variant="$area->is_active ? 'success' : 'neutral'">{{ $area->is_active ? 'Active' : 'Inactive' }}</x-pill></td>
                            <td data-label="Actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-outline btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.areas.toggle', $area) }}" @if ($area->is_active) data-confirm="Deactivate “{{ $area->name_en }}”? It will no longer be offered in forms; existing members keep it." @endif>
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm">{{ $area->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    @if ($area->members_count === 0)
                                        <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" data-confirm="Delete “{{ $area->name_en }}” permanently?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
