<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Announcements (homepage "Community Events & Announcements"). */
class AnnouncementController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::orderByDesc('is_active')->orderBy('sort_order')->orderByDesc('id')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['item' => new Announcement(['type' => 'event', 'is_active' => true,
            'sort_order' => (int) Announcement::max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $item = $this->save($request, new Announcement);
        $this->audit->record('announcement.created', $item, "Added announcement: {$item->title_en}");

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['item' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $old = $announcement->only(['title_en', 'is_active', 'starts_at', 'ends_at']);
        $this->save($request, $announcement);
        $this->audit->record('announcement.updated', $announcement, "Updated announcement: {$announcement->title_en}", $old, $announcement->only(array_keys($old)));

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();
        $this->audit->record('announcement.deleted', null, "Deleted announcement: {$announcement->title_en}");

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }

    private function save(Request $request, Announcement $item): Announcement
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Announcement::TYPES))],
            'title_en' => ['required', 'string', 'max:200'],
            'title_hi' => ['nullable', 'string', 'max:200'],
            'body_en' => ['nullable', 'string', 'max:1000'],
            'body_hi' => ['nullable', 'string', 'max:1000'],
            'event_date' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $item->fill($data);
        $item->sort_order = (int) ($data['sort_order'] ?? 0);
        $item->is_active = $request->boolean('is_active');
        $item->save();

        return $item;
    }
}
