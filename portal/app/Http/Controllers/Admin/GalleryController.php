<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Services\Gallery\GalleryService;
use App\Services\Media\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin → Gallery (admin/gallery.html): albums, photos, cover photo, visibility. */
class GalleryController extends Controller
{
    public function __construct(private readonly GalleryService $service) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        return view('admin.gallery.index', [
            'albums' => GalleryAlbum::with('cover')
                ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title_en', 'like', '%'.addcslashes($q, '%_\\').'%')
                    ->orWhere('title_hi', 'like', '%'.addcslashes($q, '%_\\').'%')))
                ->orderBy('sort_order')->orderByDesc('event_date')->orderByDesc('id')
                ->paginate(20)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        $album = new GalleryAlbum(['sort_order' => (int) GalleryAlbum::max('sort_order') + 1]);
        $album->is_active = true;

        return view('admin.gallery.form', ['album' => $album]);
    }

    public function store(Request $request): RedirectResponse
    {
        $album = $this->service->saveAlbum(new GalleryAlbum, $this->validated($request), $request->boolean('is_active'));

        return redirect()->route('admin.gallery.edit', $album->id)->with('success', "Album “{$album->title_en}” created. Now add its photos below.");
    }

    public function edit(GalleryAlbum $album): View
    {
        return view('admin.gallery.form', ['album' => $album, 'images' => $album->images()->get()]);
    }

    public function update(Request $request, GalleryAlbum $album): RedirectResponse
    {
        $this->service->saveAlbum($album, $this->validated($request), $request->boolean('is_active'));

        return redirect()->route('admin.gallery.edit', $album->id)->with('success', 'Album details saved.');
    }

    public function toggle(GalleryAlbum $album): RedirectResponse
    {
        $this->service->toggle($album);

        return back()->with('success', "“{$album->title_en}” is now ".($album->is_active ? 'visible on the website.' : 'hidden from the website.'));
    }

    public function destroy(GalleryAlbum $album): RedirectResponse
    {
        $this->service->deleteAlbum($album);

        return redirect()->route('admin.gallery.index')->with('success', "Album “{$album->title_en}” and its photos were deleted.");
    }

    // ---- Images ----

    public function upload(Request $request, GalleryAlbum $album): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:'.GalleryAlbum::MAX_UPLOAD],
            'photos.*' => ImageService::rules(true),
        ], [], ['photos' => 'photos', 'photos.*' => 'photo']);

        $added = $this->service->addImages($album, $request->file('photos'));

        return redirect()->route('admin.gallery.edit', $album->id)->with('success', "{$added} photo(s) uploaded.");
    }

    public function coverImage(GalleryImage $image): RedirectResponse
    {
        $this->service->setCover($image);

        return back()->with('success', 'Cover photo updated.');
    }

    public function destroyImage(GalleryImage $image): RedirectResponse
    {
        $this->service->deleteImage($image);

        return back()->with('success', 'Photo deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title_en' => ['required', 'string', 'max:150'],
            'title_hi' => ['nullable', 'string', 'max:150'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'description_hi' => ['nullable', 'string', 'max:1000'],
            'event_date' => ['nullable', 'date', 'before_or_equal:'.now()->addYear()->toDateString()],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [], ['title_en' => 'title (English)', 'title_hi' => 'title (Hindi)']);
    }
}
