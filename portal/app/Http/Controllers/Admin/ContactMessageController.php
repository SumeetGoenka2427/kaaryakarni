<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin → Contact Messages (inbox for the public contact form). */
class ContactMessageController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['new', 'resolved'], true) ? $request->query('status') : null;
        $search = trim((string) $request->query('q', ''));

        $messages = ContactMessage::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%")))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages, 'status' => $status, 'search' => $search,
            'newCount' => ContactMessage::where('status', 'new')->count(),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        return view('admin.contact-messages.show', ['message' => $contactMessage->load('resolver:id,name')]);
    }

    public function resolve(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->forceFill([
            'status' => 'resolved',
            'resolved_by' => $request->user('admin')->id,
            'resolved_at' => now(),
        ])->save();
        $this->audit->record('contact.resolved', $contactMessage, "Resolved message from {$contactMessage->name}");

        return redirect()->route('admin.contact-messages.index')->with('success', 'Message marked as resolved.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();
        $this->audit->record('contact.deleted', null, "Deleted message from {$contactMessage->name}");

        return redirect()->route('admin.contact-messages.index')->with('success', 'Message deleted.');
    }
}
