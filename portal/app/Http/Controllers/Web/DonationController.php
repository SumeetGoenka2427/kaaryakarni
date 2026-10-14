<?php

namespace App\Http\Controllers\Web;

use App\Enums\OfflineMethod;
use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Services\Content\PublicContent;
use App\Services\Donations\DonationService;
use App\Services\Payments\PaymentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Public donations (web/donations.html): campaigns, campaign pages and the donate form. */
class DonationController extends Controller
{
    public const PRESETS = [501, 1100, 2100, 5100, 11000];

    public function __construct(
        private readonly PublicContent $content,
        private readonly DonationService $donations,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $campaigns = $this->content->campaigns();

        return view('web.donations.index', [
            'permanent' => $campaigns->firstWhere('is_permanent', true),
            'others' => $campaigns->where('is_permanent', false)->values(),
            'campaigns' => $campaigns,
            'selected' => $campaigns->firstWhere('slug', $request->query('campaign')) ?? $campaigns->first(),
            'ticket' => $request->query('purpose') === 'ticket',
            'onlineAvailable' => $this->payments->onlineAvailable(),
        ]);
    }

    public function show(Request $request, DonationCampaign $campaign): View
    {
        abort_unless($campaign->is_active, 404);

        return view('web.donations.show', [
            'campaign' => $campaign,
            'recent' => $this->content->recentDonations($campaign->id, 8),
            'campaigns' => $this->content->campaigns(),
            'ticket' => $request->query('purpose') === 'ticket',
            'onlineAvailable' => $this->payments->onlineAvailable(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'campaign' => ['required', 'string', Rule::exists('donation_campaigns', 'slug')->where('is_active', true)->whereNull('deleted_at')],
            'type' => ['required', Rule::in(['donation', 'ticket'])],
            'amount' => ['nullable', 'required_if:type,donation', 'numeric', 'min:1', 'max:1000000'],
            'quantity' => ['nullable', 'required_if:type,ticket', 'integer', 'min:1', 'max:20'],
            'donor_name' => ['required', 'string', 'min:2', 'max:120'],
            'donor_mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'donor_email' => ['nullable', 'email:rfc', 'max:191'],
            'donor_pan' => ['nullable', 'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/'],
            'is_anonymous' => ['boolean'],
            'message' => ['nullable', 'string', 'max:500'],
            'mode' => ['required', Rule::in(['online', 'offline'])],
            'offline_method' => ['nullable', 'required_if:mode,offline', Rule::enum(OfflineMethod::class)],
            'offline_reference' => ['nullable', 'string', 'max:100'],
            'offline_paid_on' => ['nullable', 'required_if:mode,offline', 'date', 'before_or_equal:today', 'after:'.now()->subYear()->toDateString()],
            'proof' => PaymentService::proofRules(),
        ], [
            'donor_mobile.regex' => __('web.auth.mobile_invalid'),
        ], collect(['amount', 'quantity', 'name' => 'donor_name', 'mobile' => 'donor_mobile', 'email' => 'donor_email', 'pan' => 'donor_pan', 'message'])
            ->mapWithKeys(fn ($field, $key) => [$field => __('web.donations.'.(is_string($key) ? $key : $field))])
            ->merge(['offline_method' => __('web.pay.method'), 'offline_reference' => __('web.pay.reference'), 'offline_paid_on' => __('web.pay.paid_on'), 'proof' => __('web.pay.proof')])
            ->all());

        if ($data['mode'] === 'offline' && $data['offline_method'] !== 'cash' && blank($data['offline_reference'] ?? null)) {
            return back()->withInput()->withErrors(['offline_reference' => __('validation.required', ['attribute' => __('web.pay.reference')])]);
        }

        $campaign = DonationCampaign::where('slug', $data['campaign'])->firstOrFail();
        $member = $request->user('member');
        $data['is_anonymous'] = $request->boolean('is_anonymous');

        try {
            [$donation, $checkout] = $this->donations->donate($campaign, $data, $data['mode'], $member,
                collect($data)->only(['offline_method', 'offline_reference', 'offline_paid_on'])->all(), $request->file('proof'));
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($checkout !== null) {
            $payment = $donation->payment;
            $payment->meta = ['return' => 'donations.show', 'return_params' => ['campaign' => $campaign->slug]];
            $payment->save();
            if (! $member) {
                // Guests can only open the checkout for payments started in their own session.
                $request->session()->push('guest_payments', $payment->ulid);
            }

            return redirect(localized_route('payments.checkout', ['payment' => $payment->ulid]));
        }

        return redirect(localized_route('donations.show', ['campaign' => $campaign->slug]))->with('success', __('web.donations.offline_thanks'));
    }
}
