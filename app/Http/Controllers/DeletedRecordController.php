<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Oku;
use App\Services\PermissionService;
use App\Services\RecordLifecycleService;
use Illuminate\Http\Request;

class DeletedRecordController extends Controller
{
    public function index(Request $request, PermissionService $permissions)
    {
        $permissions->authorize($request->user(), 'record.restore');

        return view('admin.deleted-records', [
            'employers' => Employer::onlyTrashed()->latest('deleted_at')->get(),
            'okus' => Oku::onlyTrashed()->latest('deleted_at')->get(),
            'canPermanentlyDelete' => $permissions->allows($request->user(), 'record.permanent_delete'),
        ]);
    }

    public function restoreEmployer(Request $request, int $employer, RecordLifecycleService $lifecycle)
    {
        $record = Employer::onlyTrashed()->findOrFail($employer);
        $lifecycle->restore($request, $record);

        return back()->with('success', 'Rekod majikan telah dipulihkan.');
    }

    public function restoreOku(Request $request, int $oku, RecordLifecycleService $lifecycle)
    {
        $record = Oku::onlyTrashed()->findOrFail($oku);
        $lifecycle->restore($request, $record);

        return back()->with('success', 'Rekod OKU telah dipulihkan.');
    }

    public function permanentEmployer(Request $request, int $employer, RecordLifecycleService $lifecycle)
    {
        $record = Employer::onlyTrashed()->findOrFail($employer);
        $lifecycle->permanentDelete($request, $record);

        return back()->with('success', 'Rekod majikan dipadam secara kekal.');
    }

    public function permanentOku(Request $request, int $oku, RecordLifecycleService $lifecycle)
    {
        $record = Oku::onlyTrashed()->findOrFail($oku);
        $lifecycle->permanentDelete($request, $record);

        return back()->with('success', 'Rekod OKU dipadam secara kekal.');
    }
}
