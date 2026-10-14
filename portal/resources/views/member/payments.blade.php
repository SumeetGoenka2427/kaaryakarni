{{-- My Payments & Receipts (web/member/payments.html). --}}
<x-layouts.member :title="__('web.payments.title')">
    <section class="section section-tight">
        <div class="container">
            <h1>{{ __('web.payments.title') }}</h1>
            <p class="muted">{{ __('web.payments.intro') }}</p>

            @if ($payments->isEmpty())
                <x-empty-state :title="__('web.payments.empty')"><p>{{ __('web.payments.empty_text') }}</p></x-empty-state>
            @else
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead><tr>
                            <th scope="col">{{ __('web.payments.date') }}</th><th scope="col">{{ __('web.payments.purpose') }}</th><th scope="col">{{ __('web.payments.amount') }}</th>
                            <th scope="col">{{ __('web.payments.mode') }}</th><th scope="col">{{ __('web.payments.status') }}</th><th scope="col">{{ __('web.payments.receipt') }}</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($payments as $p)
                                <tr>
                                    <td data-label="{{ __('web.payments.date') }}">{{ ($p->paid_at ?? $p->created_at)->translatedFormat('d M Y') }}</td>
                                    <td data-label="{{ __('web.payments.purpose') }}">{{ $p->purpose->label() }}@if ($p->reference())<br><span class="muted ref-text">{{ __('web.payments.reference') }}: {{ $p->reference() }}</span>@endif</td>
                                    <td data-label="{{ __('web.payments.amount') }}">{{ inr($p->amount) }}</td>
                                    <td data-label="{{ __('web.payments.mode') }}">{{ $p->mode->label() }}@if ($p->offline_method) · {{ $p->offline_method->label() }}@endif</td>
                                    <td data-label="{{ __('web.payments.status') }}"><x-pill :status="$p->status" /></td>
                                    <td data-label="{{ __('web.payments.receipt') }}">
                                        @if ($p->receipt)
                                            <a href="{{ localized_route('member.payments.receipt', ['payment' => $p->ulid]) }}" class="btn btn-outline btn-sm">{{ __('web.payments.download') }}</a>
                                        @else — @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrap">{{ $payments->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.member>
