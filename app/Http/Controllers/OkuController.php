<?php

namespace App\Http\Controllers;

use App\Http\Requests\OkuIndexRequest;
use App\Http\Requests\SaveOkuRequest;
use App\Models\Oku;
use App\Services\BesutResidenceService;
use App\Services\JobMatchingService;
use App\Services\OkuDataService;
use App\Services\OkuImportService;
use App\Services\PermissionService;
use App\Services\RecordAuditService;
use App\Services\RecordLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OkuController extends Controller
{
    public function index(OkuIndexRequest $request, OkuDataService $data)
    {
        $filters = $request->validated();
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $perPage = (int) ($filters['per_page'] ?? $request->user()->preferences['default_page_size'] ?? 15);

        $okus = Oku::query()
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $term = $filters['search'];
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('ic_number', 'like', "%{$term}%")
                    ->orWhere('oku_card_number', 'like', "%{$term}%"));
            })
            ->when(filled($filters['category'] ?? null), fn ($query) => $query->where('oku_category', $filters['category']))
            ->when(filled($filters['employment_status'] ?? null), fn ($query) => $query->where('employment_status', $filters['employment_status']))
            ->when(filled($filters['verification_status'] ?? null), fn ($query) => $query->where('verification_status', $filters['verification_status']))
            ->when(isset($filters['age_min']), fn ($query) => $query->where('age', '>=', $filters['age_min']))
            ->when(isset($filters['age_max']), fn ($query) => $query->where('age', '<=', $filters['age_max']))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return view('oku.index', ['okus' => $okus, 'stats' => $data->getStats(), 'filters' => $filters]);
    }

    public function create()
    {
        return view('oku.form', ['oku' => new Oku]);
    }

    public function import(Request $request, OkuImportService $importer)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);

        try {
            $result = $importer->import(
                $request->file('import_file')->getRealPath(),
                $request->file('import_file')->getClientOriginalExtension(),
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['import_file' => $exception->getMessage()]);
        }

        return back()->with('import_result', $result);
    }

    public function importTemplate(OkuImportService $importer)
    {
        return response()->streamDownload(function () use ($importer) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $importer->templateHeaders());
            fputcsv($output, ['NAMA PENUH', '900101115555', 'LELAKI', '36', 'BUJANG', 'ALAMAT PENUH', 'TERENGGANU', 'BESUT', 'KAMPUNG RAJA', 'KAMPUNG RAJA', '22200', 'SEKOLAH MENENGAH', 'PH110500000001', 'FIZIKAL', 'TIDAK BEKERJA', 'PEMBANTU KEDAI', 'BANTUAN OKU TIDAK BEKERJA (BTB)']);
            fclose($output);
        }, 'templat_import_oku.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(SaveOkuRequest $request, PermissionService $permissions, RecordAuditService $audit, BesutResidenceService $residence)
    {
        $permissions->authorize($request->user(), 'oku_user.create');
        $oku = DB::transaction(function () use ($request, $residence) {
            $data = $residence->declaration($request->validated(), true);
            unset($data['oku_card_image'], $data['profile_photo']);
            $oku = Oku::query()->create($data);
            $this->storeUploadedDocuments($request, $oku);

            return $oku;
        });
        $audit->log($request, $oku, 'CREATED', [], $oku->toArray());

        return redirect()->route('oku.show', $oku)->with('success', 'Rekod OKU berjaya didaftarkan.');
    }

    public function show(Oku $oku, JobMatchingService $matching, OkuDataService $data)
    {
        return view('oku.show', ['oku' => $oku, 'matchingJobs' => $matching->getSmartRecommendations($oku), 'employmentHistory' => $oku->employments()->with('job.employer')->latest()->get(), 'welfareApplications' => $oku->welfareApplications()->latest()->get(), 'stats' => $data->getEmploymentHistory($oku)]);
    }

    public function edit(Oku $oku)
    {
        return view('oku.form', compact('oku'));
    }

    public function update(SaveOkuRequest $request, Oku $oku, PermissionService $permissions, RecordAuditService $audit, BesutResidenceService $residence)
    {
        $permissions->authorize($request->user(), 'oku_user.update');
        $before = $oku->toArray();
        DB::transaction(function () use ($request, $oku, $residence): void {
            $data = $residence->resetIfLocationChanged($oku, $residence->declaration($request->validated()));
            unset($data['oku_card_image'], $data['profile_photo']);
            $oku->update($data);
            $this->storeUploadedDocuments($request, $oku);
        });
        $audit->log($request, $oku, 'UPDATED', $before, $oku->fresh()->toArray());

        return redirect()->route('oku.show', $oku)->with('success', 'Rekod OKU berjaya dikemas kini.');
    }

    public function destroy(Request $request, Oku $oku, RecordLifecycleService $lifecycle)
    {
        $lifecycle->softDelete($request, $oku, 'oku_user.delete', $oku->name);

        return redirect()->route('oku.index')->with('success', 'OKU record deleted.');
    }

    public function findJobs(Oku $oku, JobMatchingService $matching)
    {
        return view('oku.find-jobs', ['oku' => $oku, 'matchingJobs' => $matching->findMatchingJobs($oku)]);
    }

    private function storeUploadedDocuments(SaveOkuRequest $request, Oku $oku): void
    {
        if ($request->hasFile('oku_card_image')) {
            $this->replacePrivateFile($oku, 'oku_card_image_path', $request->file('oku_card_image')->store("oku-documents/{$oku->id}/card", 'local'));
            $oku->forceFill([
                'verification_status' => 'Pending',
                'verification_notes' => null,
                'verified_at' => null,
                'verified_by' => null,
                'residence_verification_status' => 'UNVERIFIED',
                'card_address' => null,
                'card_mukim' => null,
                'residence_verification_notes' => null,
                'residence_verified_at' => null,
                'residence_verified_by' => null,
            ])->save();
        }

        if ($request->hasFile('profile_photo')) {
            $this->replacePrivateFile($oku, 'profile_photo_path', $request->file('profile_photo')->store("oku-documents/{$oku->id}/profile", 'local'));
        }
    }

    private function replacePrivateFile(Oku $oku, string $attribute, string $newPath): void
    {
        $oldPath = $oku->getAttribute($attribute);
        $oku->forceFill([$attribute => $newPath])->save();

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }
}
