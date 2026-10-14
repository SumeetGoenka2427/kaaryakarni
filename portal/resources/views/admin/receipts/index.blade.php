<x-layouts.admin title="Receipts">
    <div class="admin-page-head">
        <div><h2>Receipts</h2><p class="muted">Every successful membership payment and verified donation gets a numbered receipt (per financial year). Download the PDF to print or share.</p></div>
    </div>

    <nav class="tabs" aria-label="Receipt type">
        <a href="{{ route('admin.receipts.index', array_filter(['q' => $q])) }}" @class(['tab-btn', 'is-active' => ! $purpose])>All</a>
        @foreach (\App\Enums\PaymentPurpose::cases() as $p)
            <a href="{{ route('admin.receipts.index', array_filter(['purpose' => $p->value, 'q' => $q])) }}" @class(['tab-btn', 'is-active' => $purpose === $p])>{{ $p->label() }}</a>
        @endforeach
    </nav>

    <form method="GET" class="filters-bar">
        @if ($purpose)<input type="hidden" name="purpose" value="{{ $purpose->value }}">@endif
        <div class="field search-field"><label for="r-q">Search</label><input type="search" id="r-q" name="q" value="{{ $q }}" placeholder="Receipt number, name or mobile"></div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Search</button></div>
    </form>

    @if ($receipts->isEmpty())
        <x-empty-state title="No receipts available"><p>Receipts appear here once a membership payment succeeds or a donation is verified.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Receipt No</th><th scope="col">Name</th><th scope="col">Amount</th><th scope="col">Purpose / Campaign</th><th scope="col">Mode</th><th scope="col">Date</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($receipts as $r)
                        <tr>
                            <td data-label="Receipt No"><strong>{{ $r->receipt_no }}</strong></td>
                            <td data-label="Name">{{ $r->payment->displayName() }}</td>
                            <td data-label="Amount">{{ inr($r->payment->amount) }}</td>
                            <td data-label="Purpose / Campaign">{{ $r->payment->payable?->paymentDescription() ?? $r->payment->purpose->label() }}</td>
                            <td data-label="Mode">{{ $r->payment->mode->label() }}</td>
                            <td data-label="Date">{{ ($r->payment->paid_at ?? $r->issued_at)->format('d M Y') }}</td>
                            <td data-label="Actions">
                                <div class="row-actions">
                                    <a href="{{ route('admin.receipts.download', $r) }}" class="btn btn-outline btn-sm">Download PDF</a>
                                    @can('payments.verify')
                                        <form method="POST" action="{{ route('admin.receipts.regenerate', $r) }}">@csrf @method('PATCH')
                                            <button type="submit" class="btn btn-outline btn-sm" title="Rebuild after logo / address changes">Regenerate</button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $receipts->links() }}</div>
    @endif
</x-layouts.admin>
