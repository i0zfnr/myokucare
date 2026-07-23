<?php

namespace App\Http\Controllers;

use App\Models\Oku;
use App\Models\WelfareApplication;
use App\Services\OkuDataService;

class ReportController extends Controller
{
    public function employmentStats(OkuDataService $s)
    {
        return view('reports.employment', ['stats' => $s->dashboard()]);
    }

    public function welfareStats()
    {
        return view('reports.welfare', ['stats' => WelfareApplication::selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status')]);
    }

    public function export(string $type)
    {
        abort_unless(in_array($type, ['oku', 'welfare'], true), 404);
        $rows = $type === 'oku' ? Oku::all() : WelfareApplication::all();
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
