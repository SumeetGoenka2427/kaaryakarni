<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Models\BannerPanel;
use App\Models\BannerSlide;
use App\Models\DonationCampaign;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Homepage Banners: the three-column top banner.
 * Center = carousel slides; left/right = configurable panels.
 */
class BannerController extends Controller
{
    use HandlesImageUpload;

    private const SLIDE_WIDTHS = [640, 1280, 1920];

    private const SLIDE_RATIO = 2.0; // matches the carousel's wide frame

    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        foreach (['left' => 'membership_cta', 'right' => 'advertisement'] as $position => $mode) {
            BannerPanel::firstOrCreate(['position' => $position], ['mode' => $mode, 'is_active' => true]);
        }

        return view('admin.banners.index', [
            'slides' => BannerSlide::orderBy('sort_order')->orderBy('id')->get(),
            'panels' => BannerPanel::all()->keyBy('position'),
            'campaigns' => DonationCampaign::orderByDesc('is_permanent')->orderBy('sort_order')->pluck('title_en', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', ['slide' => new BannerSlide([
            'is_active' => true, 'sort_order' => (int) BannerSlide::max('sort_order') + 1,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $slide = $this->saveSlide($request, new BannerSlide);
        $this->audit->record('banner.slide_created', $slide, "Added slide: {$slide->title_en}");

        return redirect()->route('admin.banners.edit', $slide)->with('success', 'Slide added. Preview it below.');
    }

    public function edit(BannerSlide $banner): View
    {
        return view('admin.banners.form', ['slide' => $banner]);
    }

    public function update(Request $request, BannerSlide $banner): RedirectResponse
    {
        $old = $banner->only(['title_en', 'is_active', 'sort_order', 'starts_at', 'ends_at']);
        $this->saveSlide($request, $banner);
        $this->audit->record('banner.slide_updated', $banner, "Updated slide: {$banner->title_en}", $old, $banner->only(array_keys($old)));

        return redirect()->route('admin.banners.edit', $banner)->with('success', 'Slide saved.');
    }

    public function toggle(BannerSlide $banner): RedirectResponse
    {
        $banner->is_active = ! $banner->is_active;
        $banner->save();
        $this->audit->record('banner.slide_'.($banner->is_active ? 'activated' : 'deactivated'), $banner, $banner->title_en);

        return back()->with('success', 'Slide '.($banner->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(BannerSlide $banner, ImageService $images): RedirectResponse
    {
        $images->delete($banner->image);
        $banner->delete();
        $this->audit->record('banner.slide_deleted', null, "Deleted slide: {$banner->title_en}");

        return redirect()->route('admin.banners.index')->with('success', 'Slide deleted.');
    }

    public function updatePanel(Request $request, string $position): RedirectResponse
    {
        abort_unless(in_array($position, ['left', 'right'], true), 404);
        $panel = BannerPanel::firstOrCreate(['position' => $position], ['mode' => 'membership_cta']);

        $data = $request->validate([
            'mode' => ['required', Rule::in(array_keys(BannerPanel::MODES))],
            'campaign_id' => ['nullable', 'integer', Rule::exists('donation_campaigns', 'id')->whereNull('deleted_at')],
            'ad_url' => ['nullable', 'string', 'max:500', 'regex:#^(/(?!/)\S*|https://\S+)$#'],
            'ad_label_en' => ['nullable', 'string', 'max:120'],
            'ad_label_hi' => ['nullable', 'string', 'max:120'],
            'ad_image' => ImageService::rules(),
        ], ['ad_url.regex' => 'Use a site path like /contact or a full https:// link.']);

        $old = $panel->only(['mode', 'ad_url', 'is_active']);
        $panel->fill(collect($data)->except(['ad_image', 'campaign_id'])->all());
        $panel->campaign_id = $data['campaign_id'] ?? null;
        $panel->options = collect(BannerPanel::OPTIONS)->keys()->mapWithKeys(fn ($k) => [$k => $request->boolean("options.{$k}")])->all();
        $panel->is_active = $request->boolean('is_active');
        $this->syncImage($request, $panel, 'ad_image', 'ads', [400, 800], null, 'ad_image');
        $panel->save();
        $this->audit->record('banner.panel_updated', $panel, ucfirst($position).' panel: '.BannerPanel::MODES[$panel->mode], $old, $panel->only(array_keys($old)));

        return redirect()->route('admin.banners.index')->with('success', ucfirst($position).' panel saved.');
    }

    private function saveSlide(Request $request, BannerSlide $slide): BannerSlide
    {
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:200'],
            'title_hi' => ['nullable', 'string', 'max:200'],
            'subtitle_en' => ['nullable', 'string', 'max:300'],
            'subtitle_hi' => ['nullable', 'string', 'max:300'],
            'cta_label_en' => ['nullable', 'string', 'max:60'],
            'cta_label_hi' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'required_with:cta_label_en', 'string', 'max:500', 'regex:#^(/(?!/)\S*|https://\S+)$#'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image' => ImageService::rules(! $slide->exists),
        ], ['cta_url.regex' => 'Use a site path like /register or a full https:// link.']);

        $slide->fill(collect($data)->except('image')->all());
        $slide->sort_order = (int) ($data['sort_order'] ?? 0);
        $slide->is_active = $request->boolean('is_active');
        $this->syncImage($request, $slide, 'image', 'banners', self::SLIDE_WIDTHS, self::SLIDE_RATIO);
        $slide->save();

        return $slide;
    }
}
