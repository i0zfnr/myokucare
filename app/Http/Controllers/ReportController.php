<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmploymentReportRequest;
use App\Http\Requests\WelfareReportRequest;
use App\Models\Oku;
use App\Models\WelfareApplication;
use App\Services\EmploymentReportService;
use App\Services\WelfareReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function employmentStats(EmploymentReportRequest $request, EmploymentReportService $service)
    {
        $filters = $request->validated();

        return view('reports.employment', [
            'report' => $service->build($filters),
            'filters' => $filters,
        ]);
    }

    public function exportEmployment(EmploymentReportRequest $request, EmploymentReportService $service)
    {
        $report = $service->build($request->validated());
        $summary = $report['summary'];

        return response()->streamDownload(function () use ($report, $summary) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['LAPORAN STATISTIK PEKERJAAN OKU']);
            fputcsv($out, ['Dijana pada', $report['generated_at']->format('d/m/Y H:i')]);
            fputcsv($out, []);
            fputcsv($out, ['Metrik', 'Nilai']);
            fputcsv($out, ['Jumlah OKU', $summary['total']]);
            fputcsv($out, ['Bekerja', $summary['employed']]);
            fputcsv($out, ['Bekerja Sendiri', $summary['self_employed']]);
            fputcsv($out, ['Belum Bekerja', $summary['unemployed']]);
            fputcsv($out, ['Pekerjaan Aktif', $summary['active_employments']]);
            fputcsv($out, ['Kadar Bekerja (%)', $summary['employment_rate']]);
            fputcsv($out, []);
            fputcsv($out, ['Kategori OKU', 'Jumlah', 'Bekerja', 'Belum Bekerja', 'Kadar (%)']);
            foreach ($report['categories'] as $category => $data) {
                fputcsv($out, [$category, $data['total'], $data['working'], $data['unemployed'], $data['rate']]);
            }
            fclose($out);
        }, 'laporan-statistik-pekerjaan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function welfareStats(WelfareReportRequest $request, WelfareReportService $service)
    {
        $filters = $request->validated();

        return view('reports.welfare', [
            'report' => $service->build($filters),
            'filters' => $filters,
            'types' => $service->availableTypes(),
        ]);
    }

    public function exportWelfare(WelfareReportRequest $request, WelfareReportService $service)
    {
        $report = $service->build($request->validated());
        $summary = $report['summary'];

        return response()->streamDownload(function () use ($report, $summary) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['LAPORAN STATISTIK KEBAJIKAN OKU']);
            fputcsv($out, ['Dijana pada', $report['generated_at']->format('d/m/Y H:i')]);
            fputcsv($out, []);
            fputcsv($out, ['Metrik', 'Nilai']);
            fputcsv($out, ['Jumlah Permohonan', $summary['total']]);
            fputcsv($out, ['Menunggu', $summary['pending']]);
            fputcsv($out, ['Dalam Semakan', $summary['under_review']]);
            fputcsv($out, ['Diluluskan', $summary['approved']]);
            fputcsv($out, ['Ditolak', $summary['rejected']]);
            fputcsv($out, ['Kadar Kelulusan (%)', $summary['approval_rate']]);
            fputcsv($out, ['Purata Masa Pemprosesan (hari)', $summary['average_processing_days']]);
            fputcsv($out, []);
            fputcsv($out, ['Jenis Permohonan', 'Jumlah', 'Diluluskan', 'Peratus (%)']);
            foreach ($report['types'] as $type => $data) {
                fputcsv($out, [$type, $data['total'], $data['approved'], $data['percentage']]);
            }
            fclose($out);
        }, 'laporan-statistik-kebajikan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['oku', 'welfare'], true), 404);
        abort_unless($request->user()->hasRole('super_admin', 'jkm_officer'), 403);
        $rows = $type === 'oku' ? Oku::query()->get() : WelfareApplication::query()->get();
        $name = $type.'-report.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($out, array_keys($rows->first()->toArray()));
                foreach ($rows as $row) {
                    fputcsv($out, $row->toArray());
                }
            } fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }
}
