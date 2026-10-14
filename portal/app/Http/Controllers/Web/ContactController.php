<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('web.contact', [
            'subjects' => collect(ContactMessage::SUBJECTS)->mapWithKeys(fn ($s) => [$s => __("web.contact.subjects.{$s}")]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: real visitors never see or fill the "website" field.
        if (filled($request->input('website'))) {
            return redirect(localized_route('contact'))->with('success', __('web.contact.sent'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:191'],
            'subject' => ['required', Rule::in(ContactMessage::SUBJECTS)],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
        ], ['mobile.regex' => __('web.auth.mobile_invalid')], [
            'name' => __('web.contact.name'),
            'mobile' => __('web.contact.mobile'),
            'email' => __('web.contact.email_label'),
            'subject' => __('web.contact.subject'),
            'message' => __('web.contact.message'),
        ]);

        ContactMessage::create($data + ['locale' => app()->getLocale(), 'ip' => $request->ip()]);

        return redirect(localized_route('contact'))->with('success', __('web.contact.sent'));
    }
}
