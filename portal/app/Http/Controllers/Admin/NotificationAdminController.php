<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Broadcast;
use App\Models\Member;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Notifications (admin/notifications.html): Samaj announcements + system messages sent to members. */
class NotificationAdminController extends Controller
{
    public function __construct(private readonly Notifier $notifier, private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        return view('admin.notifications.index', [
            'broadcasts' => Broadcast::with(['area:id,name_en', 'sender:id,name'])->latest('id')->paginate(10, ['*'], 'bpage')->withQueryString(),
            'messages' => DatabaseNotification::query()
                ->whereNull('broadcast_id')
                ->where('notifiable_type', (new Member)->getMorphClass())
                ->when($q !== '', fn ($query) => $query->whereIn('notifiable_id', Member::where('name', 'like', '%'.addcslashes($q, '%_\\').'%')->orWhere('mobile', 'like', addcslashes($q, '%_\\').'%')->select('id')))
                ->with('notifiable:id,ulid,name,mobile')
                ->latest()->paginate(20, ['*'], 'mpage')->withQueryString(),
            'areas' => Area::orderBy('sort_order')->pluck('name_en', 'id'),
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:150'],
            'title_hi' => ['nullable', 'string', 'max:150'],
            'message_en' => ['required', 'string', 'max:1000'],
            'message_hi' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::in(array_keys(Broadcast::TYPES))],
            'audience' => ['required', Rule::in(array_keys(Broadcast::AUDIENCES))],
            'area_id' => ['nullable', 'required_if:audience,area', 'integer', Rule::exists('areas', 'id')],
        ], [], ['title_en' => 'title (English)', 'message_en' => 'message (English)', 'area_id' => 'area']);

        $broadcast = new Broadcast($data);
        $broadcast->area_id = $data['audience'] === 'area' ? $data['area_id'] : null;
        $broadcast->send_sms = $request->boolean('send_sms');
        $broadcast->sent_by = auth('admin')->id();
        $broadcast->save();

        $count = $this->notifier->broadcast($broadcast);

        return redirect()->route('admin.notifications.index')->with('success', "Announcement sent to {$count} member(s)".($broadcast->send_sms ? ' with SMS.' : '.'));
    }

    /** Removes the announcement from every member's inbox (SMS already sent cannot be recalled). */
    public function destroy(Broadcast $broadcast): RedirectResponse
    {
        $broadcast->delete();
        $this->audit->record('notifications.broadcast_deleted', null, "Deleted announcement “{$broadcast->title_en}”");

        return back()->with('success', 'Announcement removed from member inboxes.');
    }
}
