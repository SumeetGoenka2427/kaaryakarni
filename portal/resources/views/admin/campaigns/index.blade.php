<x-layouts.admin title="Donation Campaigns">
    <div class="admin-page-head">
        <div><h2>Donation Campaigns</h2><p class="muted">Manage the permanent Samaj Donation fund and additional campaigns. Totals update automatically when a donation is confirmed or refunded.</p></div>
        <div class="row-actions">
            <a href="{{ route('admin.donations.index') }}" class="btn btn-outline">Donation Records</a>
            @can('donations.manage')<a href="{{ route('admin.campaigns.create') }}" class="btn btn-primary">+ Add Campaign</a>@endcan
        </div>
    </div>

    @if ($campaigns->isEmpty())
        <x-empty-state title="No donation campaigns yet"><p>Add the first campaign to start collecting donations.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Campaign</th><th scope="col">Target</th><th scope="col">Raised</th><th scope="col">Donors</th><th scope="col">Tickets</th><th scope="col">Dates</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($campaigns as $c)
                        <tr>
                            <td data-label="Campaign">
                                <strong>{{ $c->title_en }}</strong>
                                @if ($c->is_permanent)<span class="pill pill-success">Permanent</span>@endif
                                @if ($c->isMissingTranslation())<span class="pill pill-danger">HI missing</span>@endif
                                @if ($c->pending_count)<br><a href="{{ route('admin.payments.index', ['status' => 'submitted', 'purpose' => 'donation']) }}" class="pill pill-warning">{{ $c->pending_count }} offline to verify</a>@endif
                            </td>
                            <td data-label="Target">{{ $c->target_amount ? inr($c->target_amount) : '—' }}</td>
                            <td data-label="Raised">{{ inr($c->raised_amount) }}@if ($c->progressPercent() !== null) <span class="muted">({{ $c->progressPercent() }}%)</span>@endif</td>
                            <td data-label="Donors">{{ number_format($c->donor_count) }}</td>
                            <td data-label="Tickets">@if ($c->sellsTickets())<span class="pill pill-neutral">{{ inr($c->ticket_price) }}</span>@else — @endif</td>
                            <td data-label="Dates">{{ $c->is_permanent ? 'Always open' : (($c->starts_at?->format('d M Y') ?? 'Now').' → '.($c->ends_at?->format('d M Y') ?? 'No end')) }}</td>
                            <td data-label="Status"><x-pill :variant="$c->isOpen() ? 'success' : 'neutral'">{{ $c->isOpen() ? 'Open' : ($c->is_active ? 'Scheduled / ended' : 'Inactive') }}</x-pill></td>
                            <td data-label="Actions">
                                <x-admin.actions>
                                    <a href="{{ route('donations.show', $c->slug) }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">View</a>
                                    @can('donations.manage')
                                        <a href="{{ route('admin.campaigns.edit', $c->id) }}" class="btn btn-outline btn-sm">Edit</a>
                                        @unless ($c->is_permanent)
                                            <form method="POST" action="{{ route('admin.campaigns.toggle', $c->id) }}" @if ($c->is_active) data-confirm="Deactivate “{{ $c->title_en }}”? It will be hidden from the website." @endif>@csrf @method('PATCH')
                                                <button type="submit" class="btn btn-outline btn-sm">{{ $c->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                                            <form method="POST" action="{{ route('admin.campaigns.destroy', $c->id) }}" data-confirm="Delete “{{ $c->title_en }}” permanently?">@csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button></form>
                                        @endunless
                                    @endcan
                                </x-admin.actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
