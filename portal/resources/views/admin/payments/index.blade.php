<x-layouts.admin title="Payments">
    <div class="admin-page-head">
        <div><h2>Payment Records</h2><p class="muted">All online and offline payments for memberships and donations. {{ $submittedCount }} offline payment(s) awaiting verification.</p></div>
        <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-outline">Export CSV</a>
    </div>

    <div class="form-alert form-alert-info" role="note">
        <span aria-hidden="true">&#8505;</span>
        <span>Online payments are marked successful only after the gateway signature is verified on our server (checkout callback or webhook) — never from a browser redirect.</span>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field search-field"><label for="f-q">Search</label><input type="search" id="f-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, mobile, reference or payment ID"></div>
        <div class="field"><label for="f-mode">Mode</label><select id="f-mode" name="mode"><option value="">All Modes</option>
            @foreach (\App\Enums\PaymentMode::options() as $v => $l)<option value="{{ $v }}" @selected(($filters['mode'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="field"><label for="f-status">Status</label><select id="f-status" name="status"><option value="">All Statuses</option>
            @foreach (\App\Enums\PaymentStatus::options() as $v => $l)@continue($v === 'created')<option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="field"><label for="f-purpose">Purpose</label><select id="f-purpose" name="purpose"><option value="">All</option>
            @foreach (\App\Enums\PaymentPurpose::options() as $v => $l)<option value="{{ $v }}" @selected(($filters['purpose'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="field"><label for="f-from">From</label><input type="date" id="f-from" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="field"><label for="f-to">To</label><input type="date" id="f-to" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>

    @if ($payments->isEmpty())
        <x-empty-state title="No payments found" icon="search"><p>Try clearing a filter.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Payer</th><th scope="col">Purpose</th><th scope="col">Amount</th><th scope="col">Mode</th><th scope="col">Reference</th><th scope="col">Status</th><th scope="col">Date</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($payments as $p)
                        <tr>
                            <td data-label="Payer"><strong>{{ $p->displayName() }}</strong><br><span class="muted">{{ $p->payer_mobile }}</span></td>
                            <td data-label="Purpose">{{ $p->purpose->label() }}</td>
                            <td data-label="Amount">{{ inr($p->amount) }}</td>
                            <td data-label="Mode">{{ $p->mode->label() }}<br><span class="muted">{{ $p->offline_method?->label() ?? ucfirst($p->provider) }}</span></td>
                            <td data-label="Reference"><span class="ref-text">{{ $p->reference() ?? '—' }}</span></td>
                            <td data-label="Status"><x-pill :status="$p->status" /></td>
                            <td data-label="Date">{{ ($p->paid_at ?? $p->created_at)->format('d M Y') }}</td>
                            <td data-label="Actions"><a href="{{ route('admin.payments.show', $p) }}" @class(['btn', 'btn-sm', 'btn-primary' => $p->status->value === 'submitted', 'btn-outline' => $p->status->value !== 'submitted'])>{{ $p->status->value === 'submitted' ? 'Review' : 'View' }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $payments->links() }}</div>
    @endif
</x-layouts.admin>
