<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Donations → Campaigns (admin/donations.html).
 * The permanent Samaj Donation campaign can be edited but never deleted or
 * deactivated (also enforced in the model).
 */
class CampaignController extends Controller
{
    use HandlesImageUpload;

    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        return view('admin.campaigns.index', [
            'campaigns' => DonationCampaign::withCount(['donations as pending_count' => fn ($q) => $q->where('status', 'submitted')])
                ->orderByDesc('is_permanent')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.campaigns.form', ['campaign' => new DonationCampaign([
            'allow_online' => true, 'allow_offline' => true, 'sort_order' => (int) DonationCampaign::max('sort_order') + 1,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $campaign = new DonationCampaign;
        $this->save($request, $campaign);
        $this->audit->record('campaign.created', $campaign, "Created campaign {$campaign->title_en}");

        return redirect()->route('admin.campaigns.index')->with('success', "Campaign “{$campaign->title_en}” created.");
    }

    public function edit(DonationCampaign $campaign): View
    {
        return view('admin.campaigns.form', compact('campaign'));
    }

    public function update(Request $request, DonationCampaign $campaign): RedirectResponse
    {
        $old = $campaign->only(['title_en', 'target_amount', 'is_active', 'starts_at', 'ends_at']);
        $this->save($request, $campaign);
        $this->audit->record('campaign.updated', $campaign, "Updated campaign {$campaign->title_en}", $old, $campaign->only(array_keys($old)));

        return redirect()->route('admin.campaigns.index')->with('success', "Campaign “{$campaign->title_en}” saved.");
    }

    public function toggle(DonationCampaign $campaign): RedirectResponse
    {
        if ($campaign->is_permanent) {
            return back()->with('error', 'The permanent Samaj Donation campaign is always active.');
        }

        $campaign->is_active = ! $campaign->is_active;
        $campaign->save();
        $this->audit->record($campaign->is_active ? 'campaign.activated' : 'campaign.deactivated', $campaign, $campaign->title_en);

        return back()->with('success', "“{$campaign->title_en}” ".($campaign->is_active ? 'activated.' : 'deactivated — hidden from the website.'));
    }

    public function destroy(DonationCampaign $campaign, ImageService $images): RedirectResponse
    {
        if ($campaign->is_permanent) {
            return back()->with('error', 'The permanent Samaj Donation campaign cannot be deleted.');
        }
        if ($campaign->donations()->exists()) {
            return back()->with('error', "“{$campaign->title_en}” has donation records and cannot be deleted. Deactivate it instead.");
        }

        $images->delete($campaign->image);
        $campaign->delete();
        $this->audit->record('campaign.deleted', null, "Deleted campaign {$campaign->title_en}");

        return back()->with('success', "Campaign “{$campaign->title_en}” deleted.");
    }

    private function save(Request $request, DonationCampaign $campaign): void
    {
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:200'],
            'title_hi' => ['nullable', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('donation_campaigns', 'slug')->ignore($campaign->id)],
            'description_en' => ['nullable', 'string', 'max:3000'],
            'description_hi' => ['nullable', 'string', 'max:3000'],
            'target_amount' => ['nullable', 'numeric', 'min:1', 'max:999999999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'ticket_label_en' => ['nullable', 'required_if_accepted:has_tickets', 'string', 'max:120'],
            'ticket_label_hi' => ['nullable', 'string', 'max:120'],
            'ticket_price' => ['nullable', 'required_if_accepted:has_tickets', 'numeric', 'min:1', 'max:100000'],
            'image' => ImageService::rules(),
        ]);

        $campaign->fill(collect($data)->except(['image', 'slug'])->all());
        $campaign->slug = $campaign->slug ?: $this->uniqueSlug($data['slug'] ?? $data['title_en']);
        if (! $campaign->is_permanent && filled($data['slug'] ?? null)) {
            $campaign->slug = Str::slug($data['slug']);
        }
        $campaign->sort_order = (int) ($data['sort_order'] ?? 0);
        $campaign->allow_online = $request->boolean('allow_online');
        $campaign->allow_offline = $request->boolean('allow_offline');
        $campaign->has_tickets = $request->boolean('has_tickets');
        $campaign->is_active = $campaign->is_permanent || $request->boolean('is_active', ! $campaign->exists);
        if ($campaign->is_permanent) {
            // Permanent fund is always open.
            $campaign->starts_at = null;
            $campaign->ends_at = null;
        }
        if (! $campaign->allow_online && ! $campaign->allow_offline) {
            $campaign->allow_offline = true;
        }
        $this->syncImage($request, $campaign, 'image', 'campaigns', [480, 960, 1440], 16 / 10);
        $campaign->save();
    }

    private function uniqueSlug(string $text): string
    {
        $base = Str::slug($text) ?: 'campaign';
        $slug = $base;
        $i = 2;
        while (DonationCampaign::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
