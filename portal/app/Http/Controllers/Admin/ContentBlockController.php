<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUpload;
use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin → Page Content. Pages/sections/fields come from config/content.php,
 * so admin can only edit blocks the public pages actually render.
 */
class ContentBlockController extends Controller
{
    use HandlesImageUpload;

    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $pages = config('content');
        $page = (string) $request->query('page', array_key_first($pages));
        abort_unless(isset($pages[$page]), 404);

        $blocks = ContentBlock::where('page', $page)->orderBy('sort_order')->orderBy('id')->get()->groupBy('section');

        return view('admin.content.index', compact('pages', 'page', 'blocks'));
    }

    public function create(string $page, string $section): View|RedirectResponse
    {
        $spec = $this->spec($page, $section);

        if ($spec['single'] && ($existing = ContentBlock::where(compact('page', 'section'))->first())) {
            return redirect()->route('admin.content.edit', $existing);
        }

        $block = new ContentBlock(['page' => $page, 'section' => $section, 'is_active' => true,
            'sort_order' => (int) ContentBlock::where(compact('page', 'section'))->max('sort_order') + 1]);

        return view('admin.content.form', compact('block', 'spec'));
    }

    public function store(Request $request, string $page, string $section): RedirectResponse
    {
        $spec = $this->spec($page, $section);
        $block = new ContentBlock(['page' => $page, 'section' => $section]);
        $this->save($request, $block, $spec);
        $this->audit->record('content.created', $block, "Added {$page} › {$spec['label']} block");

        return redirect()->route('admin.content.index', ['page' => $page])->with('success', 'Content added. It is now live on the website.');
    }

    public function edit(ContentBlock $block): View
    {
        return view('admin.content.form', ['block' => $block, 'spec' => $this->spec($block->page, $block->section)]);
    }

    public function update(Request $request, ContentBlock $block): RedirectResponse
    {
        $spec = $this->spec($block->page, $block->section);
        $old = $block->only(['title_en', 'title_hi', 'body_en', 'body_hi', 'is_active']);
        $this->save($request, $block, $spec);
        $this->audit->record('content.updated', $block, "Updated {$block->page} › {$spec['label']}", $old, $block->only(array_keys($old)));

        return redirect()->route('admin.content.index', ['page' => $block->page])->with('success', 'Content updated on the website.');
    }

    public function destroy(ContentBlock $block, ImageService $images): RedirectResponse
    {
        $spec = $this->spec($block->page, $block->section);
        abort_if($spec['single'], 403, 'This block is required by the page and can only be edited or hidden.');

        $images->delete($block->image);
        $block->delete();
        $this->audit->record('content.deleted', null, "Deleted {$block->page} › {$spec['label']}: ".($block->title_en ?? '#'.$block->id));

        return redirect()->route('admin.content.index', ['page' => $block->page])->with('success', 'Content deleted.');
    }

    private function save(Request $request, ContentBlock $block, array $spec): void
    {
        $fields = $spec['fields'];
        $rules = ['sort_order' => ['nullable', 'integer', 'min:0', 'max:999'], 'is_active' => ['boolean']];
        foreach (['label' => 120, 'title' => 200] as $field => $max) {
            if (in_array($field, $fields, true)) {
                $rules["{$field}_en"] = [$field === 'title' ? 'required' : 'nullable', 'string', "max:{$max}"];
                $rules["{$field}_hi"] = ['nullable', 'string', "max:{$max}"];
            }
        }
        if (in_array('body', $fields, true)) {
            $rules['body_en'] = [in_array('title', $fields, true) ? 'nullable' : 'required', 'string', 'max:5000'];
            $rules['body_hi'] = ['nullable', 'string', 'max:5000'];
        }
        if (in_array('link_url', $fields, true)) {
            $rules['link_url'] = ['nullable', 'string', 'max:500', 'regex:#^(/(?!/)\S*|https://\S+)$#'];
        }
        if (in_array('image', $fields, true)) {
            $rules['image'] = ImageService::rules();
        }

        $data = $request->validate($rules, ['link_url.regex' => 'Use a site path like /membership or a full https:// link.']);

        $block->fill(collect($data)->except('image')->all());
        $block->is_active = $request->boolean('is_active');
        $block->sort_order = (int) ($data['sort_order'] ?? $block->sort_order ?? 0);

        if (in_array('image', $fields, true)) {
            $this->syncImage($request, $block, 'image', 'content', [480, 960, 1440], 16 / 10);
        }

        $block->save();
    }

    private function spec(string $page, string $section): array
    {
        $spec = config("content.{$page}.sections.{$section}");
        abort_unless($spec, 404);

        return $spec + ['page' => $page, 'section' => $section, 'page_label' => config("content.{$page}.label")];
    }
}
