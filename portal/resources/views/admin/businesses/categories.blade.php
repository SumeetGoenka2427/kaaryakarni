<x-layouts.admin title="Business Categories">
    <div class="admin-page-head">
        <div><h2>Business Categories</h2><p class="muted">Categories members choose for their listing and visitors filter by. Categories in use can be deactivated but not deleted.</p></div>
        <a href="{{ route('admin.businesses.index') }}" class="btn btn-outline">&larr; Listings</a>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="admin-panel content-section" novalidate>
        @csrf
        <div class="admin-panel-head"><h3>Add Category</h3></div>
        <div class="admin-panel-body form-row-3 align-end">
            <x-field name="name_en" label="Name (English)" required maxlength="100" placeholder="e.g. Chartered Accountants" />
            <x-field name="name_hi" label="Name (हिन्दी)" maxlength="100" lang="hi" />
            <div><button type="submit" class="btn btn-primary">+ Add</button></div>
        </div>
    </form>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th scope="col">Category (English / हिन्दी)</th><th scope="col">Order</th><th scope="col">Listings</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr>
                        <td data-label="Category">
                            <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="inline-edit" id="cat-{{ $c->id }}">
                                @csrf @method('PUT')
                                <label class="visually-hidden" for="cen-{{ $c->id }}">English name</label>
                                <input type="text" id="cen-{{ $c->id }}" name="name_en" value="{{ $c->name_en }}" required maxlength="100">
                                <label class="visually-hidden" for="chi-{{ $c->id }}">Hindi name</label>
                                <input type="text" id="chi-{{ $c->id }}" name="name_hi" value="{{ $c->name_hi }}" maxlength="100" lang="hi" placeholder="हिन्दी">
                            </form>
                        </td>
                        <td data-label="Order"><label class="visually-hidden" for="cso-{{ $c->id }}">Order</label><input form="cat-{{ $c->id }}" type="number" id="cso-{{ $c->id }}" name="sort_order" value="{{ $c->sort_order }}" min="0" max="999" class="order-input"></td>
                        <td data-label="Listings">{{ $c->businesses_count }}</td>
                        <td data-label="Status"><x-pill :variant="$c->is_active ? 'success' : 'neutral'">{{ $c->is_active ? 'Active' : 'Inactive' }}</x-pill></td>
                        <td data-label="Actions">
                            <div class="row-actions">
                                <button type="submit" form="cat-{{ $c->id }}" class="btn btn-outline btn-sm">Save</button>
                                <form method="POST" action="{{ route('admin.categories.toggle', $c) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-outline btn-sm">{{ $c->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                                @if ($c->businesses_count === 0)
                                    <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="Delete “{{ $c->name_en }}”?">@csrf @method('DELETE')<button type="submit" class="btn btn-danger-outline btn-sm">Delete</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
