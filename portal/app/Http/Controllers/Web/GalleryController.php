<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\View\View;

/** Public gallery (web/gallery.html): active albums with photos, lightbox preview. */
class GalleryController extends Controller
{
    /** Photos shown per album on the overview page; the rest are on the album page. */
    public const PREVIEW = 8;

    public function index(): View
    {
        $albums = GalleryAlbum::published()
            ->with(['images' => fn ($q) => $q->limit(self::PREVIEW)])
            ->paginate(6);

        return view('web.gallery.index', ['albums' => $albums]);
    }

    public function show(GalleryAlbum $album): View
    {
        // Inactive or empty albums are not public.
        abort_unless($album->is_active && $album->images_count > 0, 404);

        return view('web.gallery.show', [
            'album' => $album,
            'images' => $album->images()->paginate(48),
        ]);
    }
}
