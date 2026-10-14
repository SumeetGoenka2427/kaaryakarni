<x-layouts.admin title="Payment">
    <div class="admin-page-head">
        <div>
            <h2>{{ inr($payment->amount) }} — {{ $payment->payable?->paymentDescription() ?? $payment->purpose->label() }}</h2>
            <p class="muted">{{ $payment->displayName() }} · <x-pill :status="$payment->status" /> · Payment ID <code>{{ $payment->ulid }}</code></p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline">&larr; All Payments</a>
    </div>

    @if ($payment->status->value === 'submitted')
        @can('payments.verify')
            <div class="admin-panel content-section">
                <div class="admin-panel-body">
                    <p class="no-margin"><strong>Offline payment awaiting verification.</strong> Check that the money was received (bank statement / cash register / cheque clearance) before verifying.</p>
                    <div class="row-actions action-gap">
                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" data-confirm="Confirm that {{ inr($payment->amount) }} was received? A receipt will be issued.">@csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary btn-sm">Verify — Money Received</button></form>
                        <button type="button" class="btn btn-danger-outline btn-sm" data-modal-open="reject-payment">Reject</button>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @if ($payment->failure_reason)
        <div class="form-alert form-alert-danger" role="status"><strong>Reason:</strong> {{ $payment->failure_reason }}</div>
    @endif

    <div class="grid grid-2 align-start">
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Details</h3></div>
            <div class="admin-panel-body">
                <dl class="detail-list">
                    <div><dt>Payer</dt><dd>@if ($payment->member)<a href="{{ route('admin.members.show', $payment->member) }}">{{ $payment->member->name }}</a>@else{{ $payment->payer_name }}@endif</dd></div>
                    <div><dt>Mobile</dt><dd>{{ $payment->payer_mobile ?? '—' }}</dd></div>
                    <div><dt>Purpose</dt><dd>{{ $payment->purpose->label() }}</dd></div>
                    <div><dt>Amount</dt><dd>{{ inr($payment->amount) }}</dd></div>
                    <div><dt>Mode</dt><dd>{{ $payment->mode->label() }} · {{ $payment->offline_method?->label() ?? ucfirst($payment->provider) }}</dd></div>
                    <div><dt>Reference</dt><dd class="ref-text">{{ $payment->reference() ?? '—' }}</dd></div>
                    @if ($payment->provider_order_id)<div><dt>Gateway order</dt><dd class="ref-text">{{ $payment->provider_order_id }}</dd></div>@endif
                    @if ($payment->offline_paid_on)<div><dt>Paid on (stated)</dt><dd>{{ $payment->offline_paid_on->format('d M Y') }}</dd></div>@endif
                    <div><dt>Created</dt><dd>{{ $payment->created_at->format('d M Y, h:i A') }}</dd></div>
                    <div><dt>Completed</dt><dd>{{ $payment->paid_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    @if ($payment->verifier)<div><dt>Reviewed by</dt><dd>{{ $payment->verifier->name }} · {{ $payment->verified_at?->format('d M Y') }}</dd></div>@endif
                    <div><dt>Receipt</dt><dd>@if ($payment->receipt)<a href="{{ route('admin.receipts.download', $payment->receipt) }}">{{ $payment->receipt->receipt_no }} (PDF)</a>@else — @endif</dd></div>
                </dl>
            </div>
        </div>
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Proof &amp; Gateway Events</h3></div>
            <div class="admin-panel-body stack">
                @if ($payment->proof)
                    @if (($payment->proof['type'] ?? 'image') === 'pdf')
                        <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Open proof (PDF)</a>
                    @else
                        <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" rel="noopener"><img src="{{ route('admin.payments.proof', $payment) }}" alt="Payment proof" class="proof-image" loading="lazy"></a>
                    @endif
                @else
                    <p class="muted">No proof uploaded.</p>
                @endif
                @foreach ($payment->events as $e)
                    <div class="audit-row"><strong>{{ $e->event }}</strong><span class="muted">{{ $e->created_at->format('d M Y, h:i:s A') }} · signature {{ $e->signature_valid ? 'valid' : 'INVALID' }}</span></div>
                @endforeach
            </div>
        </div>
    </div>

    @can('payments.verify')
        <dialog id="reject-payment" class="admin-modal" aria-labelledby="reject-payment-title" @if ($errors->has('reason')) data-open-on-load @endif>
            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                @csrf @method('PATCH')
                <div class="admin-modal-head"><h3 id="reject-payment-title">Reject Offline Payment</h3><button type="button" class="admin-modal-close" data-modal-close aria-label="Close">&times;</button></div>
                <div class="admin-modal-body form-grid">
                    <p class="muted">The payer is notified by SMS and can pay again.</p>
                    <x-field name="reason" type="textarea" :rows="3" label="Reason" required maxlength="500" placeholder="e.g. Amount not received in bank account" />
                </div>
                <div class="admin-modal-foot"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-danger-outline">Reject Payment</button></div>
            </form>
        </dialog>
    @endcan
</x-layouts.admin>
