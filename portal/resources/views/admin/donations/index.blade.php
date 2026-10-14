<x-layouts.admin title="Donation Records">
    <div class="admin-page-head">
        <div><h2>Donation Records</h2><p class="muted">All online and offline donations across every campaign. {{ number_format($totals->n) }} confirmed donations totalling {{ inr($totals->total) }}. Offline donations are verified under <a href="{{ route('admin.payments.index', ['status' => 'submitted']) }}">Payments → Review</a>.</p></div>
        <div class="row-actions">
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline">Campaigns</a>
            <a href="{{ route('admin.donations.export', request()->query()) }}" class="btn btn-outline">Export CSV</a>
        </div>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field search-field"><label for="d-q">Search</label><input type="search" id="d-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Donor name or mobile"></div>
        <div class="field"><label for="d-c">Campaign</label><select id="d-c" name="campaign"><option value="">All</option>
            @foreach ($campaigns as $id => $title)<option value="{{ $id }}" @selected((int) ($filters['campaign'] ?? 0) === $id)>{{ $title }}</option>@endforeach</select></div>
        <div class="field"><label for="d-m">Mode</label><select id="d-m" name="mode"><option value="">All Modes</option>
            @foreach (\App\Enums\PaymentMode::options() as $v => $l)<option value="{{ $v }}" @selected(($filters['mode'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="field"><label for="d-s">Status</label><select id="d-s" name="status"><option value="">All Statuses</option>
            @foreach (\App\Enums\DonationStatus::options() as $v => $l)<option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="field"><label for="d-f">From</label><input type="date" id="d-f" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="field"><label for="d-t">To</label><input type="date" id="d-t" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.donations.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</div>
    </form>

    @if ($donations->isEmpty())
        <x-empty-state title="No donations found" icon="search"><p>Try clearing a filter.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Donor</th><th scope="col">Campaign</th><th scope="col">Purpose</th><th scope="col">Amount</th><th scope="col">Mode</th><th scope="col">Status</th><th scope="col">Reference</th><th scope="col">Date</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($donations as $d)
                        <tr>
                            <td data-label="Donor"><strong>{{ $d->donor_name }}</strong>@if ($d->is_anonymous) <span class="pill pill-neutral">Anonymous</span>@endif<br><span class="muted">{{ $d->donor_mobile }}</span></td>
                            <td data-label="Campaign">{{ $d->campaign?->title_en }}</td>
                            <td data-label="Purpose">{{ $d->type === 'ticket' ? 'Ticket × '.$d->quantity : 'Donation' }}</td>
                            <td data-label="Amount">{{ inr($d->amount) }}</td>
                            <td data-label="Mode">{{ $d->payment?->mode->label() ?? '—' }}@if ($d->payment?->offline_method)<br><span class="muted">{{ $d->payment->offline_method->label() }}</span>@endif</td>
                            <td data-label="Status"><x-pill :status="$d->status" /></td>
                            <td data-label="Reference"><span class="ref-text">{{ $d->payment?->reference() ?? '—' }}</span></td>
                            <td data-label="Date">{{ $d->created_at->format('d M Y') }}</td>
                            <td data-label="Actions">
                                <div class="row-actions">
                                    @if ($d->payment)<a href="{{ route('admin.payments.show', $d->payment) }}" @class(['btn', 'btn-sm', 'btn-primary' => $d->status->value === 'submitted', 'btn-outline' => $d->status->value !== 'submitted'])>{{ $d->status->value === 'submitted' ? 'Verify' : 'Payment' }}</a>@endif
                                    @can('donations.manage')
                                        @if (in_array($d->status->value, ['successful', 'verified'], true))
                                            <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="refund-dialog"
                                                    data-form-action="{{ route('admin.donations.refund', $d) }}" data-form-label="{{ inr($d->amount) }} from {{ $d->donor_name }}">Refund</button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $donations->links() }}</div>
    @endif

    @can('donations.manage')
        <dialog id="refund-dialog" class="admin-modal" aria-labelledby="refund-title">
            <form method="POST" action="#">
                @csrf @method('PATCH')
                <div class="admin-modal-head"><h3 id="refund-title">Record Refund</h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>
                <div class="admin-modal-body form-grid">
                    <p class="muted">Refund <strong data-form-label-target></strong>. Return the money first (Razorpay dashboard for online payments, or cheque/cash for offline). This records the refund and reduces the campaign total.</p>
                    <x-field name="reason" id="refund-reason" type="textarea" :rows="3" label="Reason" required maxlength="500" />
                </div>
                <div class="admin-modal-foot"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-danger-outline">Record Refund</button></div>
            </form>
        </dialog>
    @endcan
</x-layouts.admin>
