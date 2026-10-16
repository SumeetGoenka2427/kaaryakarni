{{-- Admin report PDF (English, dompdf, A4 landscape). Inline CSS only. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 22px 26px 34px; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2733; font-size: 9.5px; }
    .head { border-bottom: 2px solid #c9a227; padding-bottom: 6px; margin-bottom: 10px; }
    .org { font-size: 14px; font-weight: bold; color: #0a1f3d; }
    .title { font-size: 12px; font-weight: bold; color: #a9791f; margin-top: 2px; }
    .meta { color: #5b6472; font-size: 9px; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #0e2a52; color: #fff; text-align: left; padding: 5px 4px; font-size: 9px; }
    td { padding: 4px; border-bottom: 1px solid #e3e0d8; vertical-align: top; }
    tr:nth-child(even) td { background: #f8f6f1; }
    .empty { padding: 20px; text-align: center; color: #5b6472; }
    .note { margin-top: 8px; color: #8a5a00; font-size: 9px; }
    .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 8px; color: #8a919c; }
</style>
</head>
<body>
    <div class="head">
        <div class="org">{{ setting('org_name_en') ?: 'Khandelwal Samaj, Jaipur' }}</div>
        <div class="title">{{ $title }}</div>
        <div class="meta">Period: {{ $period }} · Generated {{ now()->format('d M Y, h:i A') }} · {{ $rows->count() }} row(s)</div>
    </div>

    @if ($rows->isEmpty())
        <div class="empty">No records found for this period.</div>
    @else
        <table>
            <thead><tr>@foreach ($columns as $c)<th>{{ $c }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>@foreach ($row as $value)<td>{{ $value }}</td>@endforeach</tr>
                @endforeach
            </tbody>
        </table>
        @if ($truncated)
            <p class="note">Only the first {{ number_format($rows->count()) }} rows are included in the PDF. Use the Excel or CSV export for the full report.</p>
        @endif
    @endif

    <div class="footer">Confidential — for Samaj office use only.</div>
</body>
</html>
