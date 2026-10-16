<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Content\PublicContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Public content pages. All text/images come from admin-managed, cached content. */
class PageController extends Controller
{
    public function __construct(private readonly PublicContent $content) {}

    public function home(): View
    {
        return view('web.home', [
            'slides' => $this->content->slides(),
            'panels' => $this->content->panels(),
            'about' => $this->content->block('home', 'about'),
            'stats' => $this->content->stats(),
            'announcements' => $this->content->announcements(3),
            'campaigns' => $this->content->campaigns()->take(3),
            'businesses' => $this->content->featuredBusinesses(4),
            'links' => $this->content->blocks('home', 'links'),
        ]);
    }

    public function about(): View
    {
        return view('web.about', [
            'history' => $this->content->block('about', 'history'),
            'values' => $this->content->blocks('about', 'values'),
            'leaders' => $this->content->committee('leadership'),
            'hasGeneral' => $this->content->committee('general')->isNotEmpty(),
        ]);
    }

    public function committee(Request $request): View
    {
        $general = $this->content->committee('general');
        $show = min(max(20, (int) $request->query('show', 20)), max(20, $general->count()));

        return view('web.committee', [
            'leaders' => $this->content->committee('leadership'),
            'general' => $general->take($show),
            'generalTotal' => $general->count(),
            'show' => $show,
        ]);
    }

}
