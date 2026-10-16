{{-- Member → Notifications (web/member/notifications.html). --}}
<x-layouts.member :title="__('web.notifications.title')">
    <section class="section section-tight">
        <div class="container container-narrow">
            <div class="page-title-row">
                <div>
                    <h1>{{ __('web.notifications.title') }}</h1>
                    <p class="muted">{{ __('web.notifications.intro') }}</p>
                </div>
                @if ($unread)
                    <form method="POST" action="{{ localized_route('member.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">{{ __('web.notifications.mark_all') }}</button>
                    </form>
                @endif
            </div>

            @if ($items->isEmpty())
                <x-empty-state :title="__('web.notifications.empty')">
                    <p>{{ __('web.notifications.empty_text') }}</p>
                </x-empty-state>
            @else
                <ul class="notif-list">
                    @foreach ($items as $n)
                        <li class="card notif-item @if ($n['unread']) is-unread @endif">
                            <div class="card-body notif-body">
                                <span class="pill pill-neutral notif-type">{{ $n['type'] }}</span>
                                <div class="notif-text">
                                    <h2 class="notif-title">
                                        {{ $n['title'] }}
                                        @if ($n['unread'])<span class="pill pill-warning">{{ __('web.notifications.new') }}</span>@endif
                                    </h2>
                                    <p class="muted notif-message">{{ $n['message'] }}</p>
                                    <p class="muted notif-date"><time datetime="{{ $n['date']->toIso8601String() }}">{{ $n['date']->translatedFormat('d M Y, h:i A') }}</time></p>
                                </div>
                                @if ($n['link'] || $n['unread'])
                                    <a href="{{ localized_route('member.notifications.open', ['id' => $n['id']]) }}" class="btn btn-outline btn-sm notif-action">{{ $n['link'] ? __('web.notifications.view') : __('web.notifications.mark_read') }}</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="pagination-wrap">{{ $items->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.member>
