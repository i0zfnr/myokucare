<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ManualReview;
use App\Services\Identity\SecureImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManualReviewController extends Controller
{
    public function index()
    {
        return view('identity-verification.reviews', [
            'reviews' => ManualReview::query()->with('session.user')->latest()->paginate(20),
        ]);
    }

    public function show(ManualReview $manualReview)
    {
        return view('identity-verification.review-show', [
            'review' => $manualReview->load('session.user.oku', 'session.documents.fields', 'session.comparison'),
        ]);
    }

    public function document(Request $request, ManualReview $manualReview, $document, SecureImageService $images)
    {
        $document = $manualReview->session->documents()->findOrFail($document);
        abort_unless(Storage::disk('local')->exists($document->processed_file_path), 404);
        $this->audit($request, $manualReview, 'identity_document_accessed');

        return response($images->decryptedBytes($document->processed_file_path), 200, [
            'Content-Type' => $document->processing_metadata['processedMime'] ?? 'image/jpeg',
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(Request $request, ManualReview $manualReview)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'NEEDS_RESUBMISSION'])]]);
        $manualReview->update(['status' => $data['status'], 'reviewer_id' => $request->user()->id, 'reviewed_at' => now()]);
        $session = $manualReview->session;
        $userStatus = match ($data['status']) {
            'APPROVED' => 'VERIFIED',
            'REJECTED' => 'REJECTED',
            default => 'RESUBMISSION_REQUIRED',
        };
        $session->update(['status' => $userStatus]);
        $session->user->update([
            'mykad_verification_status' => $userStatus,
            'mykad_verified_at' => $data['status'] === 'APPROVED' ? now() : null,
            'mykad_resubmission_required' => $data['status'] === 'NEEDS_RESUBMISSION',
        ]);
        $this->audit($request, $manualReview, 'identity_manual_review_completed', ['status' => $data['status']]);

        return redirect()->route('identity-reviews.show', $manualReview)->with('success', 'Keputusan semakan telah disimpan.');
    }

    private function audit(Request $request, ManualReview $review, string $action, array $changes = []): void
    {
        ActivityLog::query()->create([
            'actor_id' => $request->user()->id, 'subject_user_id' => $review->session->user_id,
            'action' => $action, 'changes' => ['review_id' => $review->id] + $changes,
            'ip_address' => $request->ip(), 'user_agent' => str($request->userAgent())->limit(1000),
        ]);
    }
}
