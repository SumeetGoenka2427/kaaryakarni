<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditLogger;
use App\Services\Reports\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\Response;

/** Admin → Reports (admin/reports.html): on-screen preview + CSV / Excel / PDF export. */
class ReportController extends Controller
{
    /** PDF is for printing/sharing summaries; very large lists should use Excel. */
    private const PDF_MAX_ROWS = 2000;

    public function __construct(private readonly ReportService $reports, private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $preview = $request->query('preview');
        abort_if($preview !== null && ! isset(ReportService::REPORTS[$preview]), 404);

        return view('admin.reports.index', [
            'reports' => ReportService::REPORTS,
            'from' => $from,
            'to' => $to,
            'previewKey' => $preview,
            'preview' => $preview ? $this->reports->preview($preview, $from, $to) : null,
        ]);
    }

    public function export(Request $request, string $report, string $format): Response
    {
        abort_unless(isset(ReportService::REPORTS[$report]) && in_array($format, ['csv', 'xlsx', 'pdf'], true), 404);
        [$from, $to] = $this->range($request);

        $title = $this->reports->title($report);
        $period = $this->periodLabel($from, $to);
        $filename = str($title)->slug().'-'.now()->format('Y-m-d').'.'.$format;
        $this->audit->record('reports.exported', null, "Exported {$title} ({$period}) as ".strtoupper($format), [], array_filter(['from' => $from?->toDateString(), 'to' => $to?->toDateString()]));

        if ($format === 'pdf') {
            $rows = collect();
            foreach ($this->reports->rows($report, $from, $to) as $row) {
                if ($rows->count() >= self::PDF_MAX_ROWS) {
                    break;
                }
                $rows->push($row);
            }

            return Pdf::loadView('pdf.report', [
                'title' => $title, 'period' => $period, 'rows' => $rows,
                'columns' => array_keys($rows->first() ?? []), 'truncated' => $rows->count() >= self::PDF_MAX_ROWS,
            ])->setPaper('a4', 'landscape')->setOption(['isFontSubsettingEnabled' => true])->download($filename);
        }

        // CSV / Excel are streamed row by row, so large reports use little memory.
        return response()->streamDownload(function () use ($report, $from, $to, $format) {
            // CSV gets a BOM so Excel shows Hindi names correctly.
            $writer = SimpleExcelWriter::create('php://output', $format);
            foreach ($this->reports->rows($report, $from, $to) as $row) {
                $writer->addRow($row);
            }
            $writer->close();
        }, $filename, ['Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', Rule::when($request->filled('from'), 'after_or_equal:from')],
        ]);

        return [
            isset($data['from']) ? Carbon::parse($data['from']) : null,
            isset($data['to']) ? Carbon::parse($data['to']) : null,
        ];
    }

    private function periodLabel(?Carbon $from, ?Carbon $to): string
    {
        return match (true) {
            $from && $to => $from->format('d M Y').' – '.$to->format('d M Y'),
            (bool) $from => 'from '.$from->format('d M Y'),
            (bool) $to => 'up to '.$to->format('d M Y'),
            default => 'all dates',
        };
    }
}
