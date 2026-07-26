<?php

namespace App\Http\Controllers;

use App\Models\ExportAuditLog;
use App\Services\ExportService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        return view('exports.index', [
            'exports' => ExportAuditLog::query()->where('exported_by_user_id', $request->user()->id)->latest()->paginate(15),
            'types' => ExportService::TYPES, 'formats' => ExportService::FORMATS, 'purposes' => ExportService::PURPOSES,
        ]);
    }

    public function store(Request $request, ExportService $service)
    {
        $data = $request->validate([
            'format' => ['required', Rule::in(ExportService::FORMATS)],
            'report_type' => ['required', Rule::in(ExportService::TYPES)],
            'fields' => ['nullable', 'array'], 'fields.*' => ['string', 'max:50'],
            'purpose' => ['required', Rule::in(ExportService::PURPOSES)],
            'language' => ['nullable', Rule::in(['BM', 'EN', 'ZH_CN'])],
            'content_mode' => ['nullable', Rule::in(['ORIGINAL', 'TRANSLATED', 'DUAL'])],
            'filters' => ['nullable', 'array'],
        ]);
        $export = $service->generate($request, $data);

        return $request->expectsJson()
            ? response()->json(['exportId' => $export->id, 'status' => $export->status, 'format' => $export->format, 'expiresAt' => $export->expires_at, 'downloadUrl' => $service->downloadUrl($export)], 201)
            : redirect()->route('exports.index')->with('success', __('export.generated'));
    }

    public function status(Request $request, ExportAuditLog $export, ExportService $service)
    {
        $this->authorizeExport($request, $export);

        return response()->json(['exportId' => $export->id, 'status' => $export->status, 'expiresAt' => $export->expires_at, 'downloadUrl' => $export->status === 'READY' ? $service->downloadUrl($export) : null]);
    }

    public function download(Request $request, ExportAuditLog $export)
    {
        $this->authorizeExport($request, $export);
        abort_if($export->expires_at->isPast(), 410, 'EXPORT_LINK_EXPIRED');
        abort_unless($export->status === 'READY' && Storage::disk('local')->exists($export->generated_file_path), 404);
        $export->update(['downloaded_at' => now()]);

        return Storage::disk('local')->download($export->generated_file_path, 'report_'.$export->id.'.'.strtolower($export->format), [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, ExportAuditLog $export)
    {
        $this->authorizeExport($request, $export);
        Storage::disk('local')->delete($export->generated_file_path);
        $export->update(['status' => 'DELETED', 'generated_file_path' => null]);

        return response()->noContent();
    }

    private function authorizeExport(Request $request, ExportAuditLog $export): void
    {
        $isOwner = $export->exported_by_user_id === $request->user()->id;
        $isAuthorisedJkm = $request->user()->hasRole('super_admin', 'jkm_officer') && app(PermissionService::class)->allows($request->user(), 'report.generate');
        abort_unless($isOwner || $isAuthorisedJkm, 403);
    }
}
