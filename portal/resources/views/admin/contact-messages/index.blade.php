<x-layouts.admin title="Contact Messages">
    <div class="admin-page-head">
        <div>
            <h2>Contact Messages</h2>
            <p class="muted">Messages sent from the website’s Contact page. {{ $newCount }} new.</p>
        </div>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" data-autosubmit>
                <option value="">All</option>
                <option value="new" @selected($status === 'new')>New</option>
                <option value="resolved" @selected($status === 'resolved')>Resolved</option>
            </select>
        </div>
        <div class="field search-field">
            <label for="q">Search</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Name, mobile or message text">
        </div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
    </form>

    @if ($messages->isEmpty())
        <x-empty-state title="No messages found" />
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">From</th><th scope="col">Subject</th><th scope="col">Message</th><th scope="col">Received</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($messages as $m)
                        <tr>
                            <td data-label="From"><strong>{{ $m->name }}</strong><br><span class="muted">{{ $m->mobile }}</span></td>
                            <td data-label="Subject">{{ $m->subjectLabel() }}</td>
                            <td data-label="Message">{{ \Illuminate\Support\Str::limit($m->message, 90) }}</td>
                            <td data-label="Received">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                            <td data-label="Status"><x-pill :variant="$m->status === 'new' ? 'warning' : 'success'">{{ ucfirst($m->status) }}</x-pill></td>
                            <td data-label="Actions"><a href="{{ route('admin.contact-messages.show', $m) }}" class="btn btn-outline btn-sm">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $messages->links() }}</div>
    @endif
</x-layouts.admin>
