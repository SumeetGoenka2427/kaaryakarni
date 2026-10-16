@php
    $range = array_filter(['from' => $from?->toDateString(), 'to' => $to?->toDateString()]);
@endphp
<x-layouts.admin title="Reports">
    <div class="admin-page-head">
        <div>
            <h2>Reports &amp; Exports</h2>
            <p class="muted">Preview any report on screen, or download it as Excel, CSV (opens directly in Excel) or PDF. Every export is recorded in the audit log.</p>
        </div>
    </div>

    <div class="admin-panel content-section">
        <div class="admin-panel-body">
            <form method="GET" class="filters-bar no-margin">
                @if ($previewKey)<input type="hidden" name="preview" value="{{ $previewKey }}">@endif
                <div class="field"><label for="date-from">From Date</label><input type="date" id="date-from" name="from" value="{{ $from?->toDateString() }}"></div>
                <div class="field"><label for="date-to">To Date</label><input type="date" id="date-to" name="to" value="{{ $to?->toDateString() }}"></div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    @if ($range)<a href="{{ route('admin.reports.index', array_filter(['preview' => $previewKey])) }}" class="btn btn-outline btn-sm">Clear dates</a>@endif
                </div>
            </form>
            @error('to')<p class="form-alert form-alert-danger" role="alert">{{ $message }}</p>@enderror
            <p class="muted report-note">When dates are set, every report below is limited to records created within that range (the Area-wise report counts members who registered in that range).</p>
        </div>
    </div>

    @if ($preview)
        <div class="admin-panel content-section" id="preview">
            <div class="admin-panel-head">
                <h3>{{ $reports[$previewKey][0] }} — preview</h3>
                <a href="{{ route('admin.reports.index', $range) }}" class="btn btn-outline btn-sm">Close preview</a>
            </div>
            <div class="admin-panel-body">
                <p class="muted">{{ number_format($preview['total']) }} row(s){{ $preview['total'] > $preview['rows']->count() ? ', showing the first '.$preview['rows']->count().'. Download the report for all rows.' : '.' }}</p>
            </div>
            @if ($preview['rows']->isEmpty())
                <x-empty-state title="No records for this period" icon="search"><p>Try a wider date range or clear the dates.</p></x-empty-state>
            @else
                <div class="data-table-wrap table-flush report-preview">
                    <table class="data-table">
                        <thead><tr>@foreach ($preview['columns'] as $col)<th scope="col">{{ $col }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($preview['rows'] as $row)
                                <tr>@foreach ($row as $col => $value)<td data-label="{{ $col }}">{{ $value }}</td>@endforeach</tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-2 grid-compact align-start">
        @foreach ($reports as $key => [$title, $desc])
            <div class="admin-panel report-card">
                <div class="admin-panel-head"><h3>{{ $title }}</h3></div>
                <div class="admin-panel-body">
                    <p class="muted no-margin">{{ $desc }}</p>
                    <div class="report-actions">
                        <a href="{{ route('admin.reports.index', $range + ['preview' => $key]) }}#preview" class="btn btn-primary btn-sm">Preview</a>
                        @can('reports.export')
                            <a href="{{ route('admin.reports.export', ['report' => $key, 'format' => 'xlsx'] + $range) }}" class="btn btn-outline btn-sm">Excel</a>
                            <a href="{{ route('admin.reports.export', ['report' => $key, 'format' => 'csv'] + $range) }}" class="btn btn-outline btn-sm">CSV</a>
                            <a href="{{ route('admin.reports.export', ['report' => $key, 'format' => 'pdf'] + $range) }}" class="btn btn-outline btn-sm">PDF</a>
                        @else
                            <span class="muted">Downloading needs the “Export reports” permission.</span>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
