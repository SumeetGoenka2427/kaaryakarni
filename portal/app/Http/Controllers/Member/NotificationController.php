<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/** Member → Notifications (web/member/notifications.html): system messages + Samaj announcements. */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user('member')->notifications()->paginate(20);
        $broadcasts = Broadcast::whereIn('id', $notifications->pluck('broadcast_id')->filter()->unique())->get()->keyBy('id');

        return view('member.notifications', [
            'items' => $notifications->through(fn (DatabaseNotification $n) => $this->present($n, $broadcasts)),
            'unread' => $request->user('member')->unreadNotifications()->count(),
        ]);
    }

    /** Mark one as read and go to the page it is about. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user('member')->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();
        $route = $notification->data['route'] ?? null;

        return redirect($route && Route::has($route) ? localized_route($route) : localized_route('member.notifications'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user('member')->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', __('web.notifications.all_read'));
    }

    private function present(DatabaseNotification $n, $broadcasts): array
    {
        $data = $n->data;
        $broadcast = $broadcasts->get($n->broadcast_id);
        $locale = app()->getLocale();

        return [
            'id' => $n->id,
            'type' => __('web.notifications.types.'.($data['type'] ?? 'announcement')),
            'title' => $broadcast ? $broadcast->t('title') : __('web.notifications.titles.'.($data['kind'] ?? 'general')),
            'message' => $broadcast ? $broadcast->t('message') : ($data['message_'.$locale] ?? $data['message_en'] ?? ''),
            'link' => ! empty($data['route']),
            'unread' => $n->read_at === null,
            'date' => $n->created_at,
        ];
    }
}
