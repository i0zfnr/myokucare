<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessVerificationRequest;
use App\Models\ActivityLog;
use App\Models\VerificationSession;
use App\Services\Identity\SecureImageService;
use App\Services\Identity\VerificationWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IdentityVerificationController extends Controller
{
    public function show(Request $request)
    {
        return view('identity-verification.show', [
            'session' => $request->user()->verificationSessions()->latest()->first(),
            'status' => $request->user()->mykad_verification_status,
        ]);
    }

    public function createSession(Request $request)
    {
        abort_unless($request->user()->hasRole('oku_user'), 403);
        if (! $request->boolean('consent')) {
            return response()->json(['message' => 'Persetujuan diperlukan.', 'errors' => ['consent' => ['Persetujuan diperlukan.']]], 422);
        }
        $session = VerificationSession::query()->create([
            'user_id' => $request->user()->id,
            'status' => 'SUBMISSION_IN_PROGRESS',
            'expires_at' => now()->addHours(config('identity_verification.session_expiry_hours')),
            'consent_accepted_at' => now(),
        ]);
        $request->user()->update([
            'mykad_verification_status' => 'SUBMISSION_IN_PROGRESS',
            'mykad_verification_session_id' => $session->id,
        ]);
        $this->audit($request, 'identity_verification_started', ['session_id' => $session->id]);

        return response()->json(['sessionId' => $session->id, 'status' => $session->status], 201);
    }

    public function upload(Request $request, VerificationSession $session, SecureImageService $images)
    {
        $this->authoriseSession($request, $session);
        $validator = Validator::make($request->all(), [
            'document_type' => ['required', Rule::in(['oku_front', 'oku_back', 'mykad_front', 'mykad_back'])],
            'image' => ['required', 'file', 'max:'.config('identity_verification.max_upload_kb')],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Fail tidak sah.', 'errors' => $validator->errors()->toArray()], 422);
        }
        try {
            $result = $images->storeAndProcess($request->file('image'), $session->id, $request->string('document_type'));
        } catch (ValidationException $exception) {
            return response()->json(['message' => 'Fail tidak sah.', 'errors' => $exception->errors()], 422);
        }
        $issues = $result['metadata']['issues'] ?? [];
        $accepted = empty($issues) && $result['metadata']['qualityScore'] >= config('identity_verification.min_image_quality_score');
        $existing = $session->documents()->where('document_type', $request->string('document_type'))->first();
        if ($existing) {
            Storage::disk('local')->delete([$existing->original_file_path, $existing->processed_file_path]);
        }
        $document = $session->documents()->updateOrCreate(['document_type' => $request->string('document_type')], [
            'original_file_path' => $result['original'],
            'processed_file_path' => $result['processed'],
            'quality_status' => $accepted ? 'ACCEPTED' : 'REJECTED',
            'quality_score' => $result['metadata']['qualityScore'],
            'quality_issues' => $issues,
            'processing_metadata' => $result['metadata'],
        ]);

        return response()->json([
            'documentId' => $document->id,
            'accepted' => $accepted,
            'qualityScore' => $document->quality_score,
            'issues' => $issues,
            'processedImageUrl' => route('identity-verification.document', [$session, $document]),
        ], $accepted ? 201 : 422);
    }

    public function document(Request $request, VerificationSession $session, $document, SecureImageService $images)
    {
        $this->authoriseSession($request, $session);
        $document = $session->documents()->findOrFail($document);
        abort_unless(Storage::disk('local')->exists($document->processed_file_path), 404);

        return response($images->decryptedBytes($document->processed_file_path), 200, [
            'Content-Type' => $document->processing_metadata['processedMime'] ?? 'image/jpeg',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function process(ProcessVerificationRequest $request, VerificationSession $session, VerificationWorkflowService $workflow)
    {
        $this->authoriseSession($request, $session);
        $this->ensureActive($session);
        $workflow->process($session, $request->validated());

        return $this->payload($session->fresh(['documents.fields', 'documents.qrResults']));
    }

    public function verify(Request $request, VerificationSession $session, VerificationWorkflowService $workflow)
    {
        $this->authoriseSession($request, $session);
        $this->ensureActive($session);
        $verified = $workflow->verify($session);
        $this->audit($request, 'identity_verification_completed', ['session_id' => $session->id, 'status' => $verified->status]);

        return $this->payload($verified);
    }

    public function status(Request $request, ?VerificationSession $session = null)
    {
        if (! $session) {
            return response()->json(['status' => $request->user()->mykad_verification_status]);
        }
        $this->authoriseSession($request, $session);

        return $this->payload($session->load(['documents.fields', 'documents.qrResults', 'comparison', 'manualReview']));
    }

    public function resubmit(Request $request, VerificationSession $session)
    {
        $this->authoriseSession($request, $session);
        $session->update(['status' => 'SUBMISSION_IN_PROGRESS', 'expires_at' => now()->addHours(config('identity_verification.session_expiry_hours'))]);
        $request->user()->update(['mykad_verification_status' => 'SUBMISSION_IN_PROGRESS', 'mykad_resubmission_required' => false]);

        return response()->json(['sessionId' => $session->id, 'status' => $session->status]);
    }

    public function manualReview(Request $request, VerificationSession $session, VerificationWorkflowService $workflow)
    {
        $this->authoriseSession($request, $session);
        $review = $workflow->manualReview($session, ['USER_REQUESTED_REVIEW']);
        $session->update(['status' => 'MANUAL_REVIEW_REQUIRED']);
        $request->user()->update(['mykad_verification_status' => 'MANUAL_REVIEW_REQUIRED']);

        return response()->json(['status' => $review->status], 202);
    }

    private function authoriseSession(Request $request, VerificationSession $session): void
    {
        abort_unless($session->user_id === $request->user()->id, 403);
    }

    private function ensureActive(VerificationSession $session): void
    {
        abort_if($session->expires_at->isPast(), 410, 'VERIFICATION_SESSION_EXPIRED');
    }

    private function payload(VerificationSession $session)
    {
        $session->loadMissing(['documents.fields', 'documents.qrResults', 'comparison', 'manualReview']);
        $document = fn ($type) => $session->documents->firstWhere('document_type', $type);
        $field = fn ($doc, $name) => $doc?->fields->firstWhere('field_name', $name);
        $oku = $document('oku_front');
        $mykad = $document('mykad_front');

        return response()->json([
            'sessionId' => $session->id,
            'status' => $session->status,
            'okuCard' => [
                'frontAccepted' => $oku?->quality_status === 'ACCEPTED',
                'backAccepted' => $document('oku_back')?->quality_status === 'ACCEPTED',
                'qrDetected' => $session->documents->flatMap->qrResults->isNotEmpty(),
                'qrVerified' => $session->documents->flatMap->qrResults->contains('provider_status', 'VERIFIED_BY_PROVIDER'),
                'name' => $field($oku, 'name')?->encrypted_value,
                'nricMasked' => $field($oku, 'nric')?->masked_value,
                'okuRegistrationNumber' => $field($oku, 'oku_registration_number')?->encrypted_value,
            ],
            'mykad' => [
                'frontAccepted' => $mykad?->quality_status === 'ACCEPTED',
                'backAccepted' => $document('mykad_back')?->quality_status === 'ACCEPTED',
                'name' => $field($mykad, 'name')?->encrypted_value,
                'nricMasked' => $field($mykad, 'nric')?->masked_value,
            ],
            'comparison' => $session->comparison ? [
                'nricMatch' => $session->comparison->nric_match,
                'nameMatch' => $session->comparison->name_match,
                'nameSimilarity' => $session->comparison->name_similarity,
            ] : null,
            'issues' => $session->comparison?->reason_codes ?? $session->documents->flatMap(fn ($doc) => $doc->quality_issues ?? [])->unique()->values(),
            'requiresManualReview' => (bool) $session->manualReview,
        ]);
    }

    private function audit(Request $request, string $action, array $changes): void
    {
        ActivityLog::query()->create([
            'actor_id' => $request->user()->id, 'subject_user_id' => $request->user()->id,
            'action' => $action, 'changes' => $changes, 'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(1000),
        ]);
    }
}
