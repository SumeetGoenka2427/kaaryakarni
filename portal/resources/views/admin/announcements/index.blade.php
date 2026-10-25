<x-layouts.admin title="Announcements">
    <div class="admin-page-head">
        <div>
            <h2>Events &amp; Announcements</h2>
            <p class="muted">Shown in the homepage “Community Events &amp; Announcements” section (latest 3 visible items). Use the start/end dates to schedule them.</p>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">+ Add Announcement</a>
    </div>

    @if ($announcements->isEmpty())
        <x-empty-state title="No announcements yet">
            <p>The homepage section stays hidden until you <a href="{{ route('admin.announcements.create') }}">add an announcement</a>.</p>
        </x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Title</th><th scope="col">Type</th><th scope="col">Event Date</th><th scope="col">Schedule</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($announcements as $a)
                        <tr>
                            <td data-label="Title"><strong>{{ $a->title_en }}</strong>@if ($a->isMissingTranslation())<span class="pill pill-danger">HI missing</span>@endif</td>
                            <td data-label="Type">{{ \App\Models\Announcement::TYPES[$a->type] }}</td>
                            <td data-label="Event Date">{{ $a->event_date?->format('d M Y') ?? '—' }}</td>
                            <td data-label="Schedule">{{ $a->starts_at?->format('d M Y') ?? 'Now' }} → {{ $a->ends_at?->format('d M Y') ?? 'No end' }}</td>
                            <td data-label="Status"><x-pill :variant="$a->is_active ? 'success' : 'neutral'">{{ $a->is_active ? 'Active' : 'Hidden' }}</x-pill></td>
                            <td data-label="Actions">
                                <x-admin.actions>
                                    <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-outline btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" data-confirm="Delete this announcement?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button>
                                    </form>
                                </x-admin.actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $announcements->links() }}</div>
    @endif
</x-layouts.admin>
