<x-layouts.admin title="Committee">
    <div class="admin-page-head">
        <div>
            <h2>Samaj Committee</h2>
            <p class="muted">Leadership members appear with a photo on the About and Committee pages; general members appear with name and designation only.</p>
        </div>
        <a href="{{ route('admin.committee.create') }}" class="btn btn-primary">+ Add Member</a>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field">
            <label for="tier">Group</label>
            <select id="tier" name="tier" data-autosubmit>
                <option value="">All ({{ $counts->sum() }})</option>
                @foreach (\App\Models\CommitteeMember::TIERS as $key => $label)
                    <option value="{{ $key }}" @selected($tier === $key)>{{ $label }} ({{ $counts[$key] ?? 0 }})</option>
                @endforeach
            </select>
        </div>
        <div class="field search-field">
            <label for="q">Search</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Name or designation">
        </div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
    </form>

    @if ($members->isEmpty())
        <x-empty-state title="No committee members found">
            <p><a href="{{ route('admin.committee.create') }}">Add the first committee member</a>.</p>
        </x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Member</th><th scope="col">Designation</th><th scope="col">Group</th><th scope="col">Order</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($members as $m)
                        <tr>
                            <td data-label="Member">
                                <div class="flex gap-3 align-center">
                                    @if ($m->photo)<x-img :image="$m->photo" alt="" class="table-avatar" sizes="40px" />@else<span class="table-avatar initials-avatar" aria-hidden="true">{{ $m->initials() }}</span>@endif
                                    <span><strong>{{ $m->name_en }}</strong>@if ($m->name_hi)<br><span class="muted" lang="hi">{{ $m->name_hi }}</span>@endif</span>
                                </div>
                            </td>
                            <td data-label="Designation">{{ $m->designation_en }}</td>
                            <td data-label="Group">{{ $m->tier === 'leadership' ? 'Leadership' : 'General' }}</td>
                            <td data-label="Order">{{ $m->sort_order }}</td>
                            <td data-label="Status"><x-pill :variant="$m->is_active ? 'success' : 'neutral'">{{ $m->is_active ? 'Visible' : 'Hidden' }}</x-pill></td>
                            <td data-label="Actions">
                                <x-admin.actions>
                                    <a href="{{ route('admin.committee.edit', $m) }}" class="btn btn-outline btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.committee.destroy', $m) }}" data-confirm="Remove {{ $m->name_en }} from the committee?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm">Remove</button>
                                    </form>
                                </x-admin.actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $members->links() }}</div>
    @endif
</x-layouts.admin>
