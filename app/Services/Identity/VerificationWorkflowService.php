<?php

namespace App\Services\Identity;

use App\Contracts\OkuVerificationProvider;
use App\Models\IdentityComparison;
use App\Models\ManualReview;
use App\Models\VerificationDocument;
use App\Models\VerificationSession;
use Illuminate\Support\Facades\DB;

class VerificationWorkflowService
{
    public function __construct(
        private OcrExtractionService $ocr,
        private IdentityComparisonService $comparison,
        private IdentityNormalizer $normalizer,
        private QrPayloadService $qr,
        private OkuVerificationProvider $provider,
    ) {}

    public function process(VerificationSession $session, array $input): VerificationSession
    {
        DB::transaction(function () use ($session): void {
            // Browser-provided OCR text, confidence scores and QR payloads are
            // untrusted. Until a server-side provider is configured, JKM must
            // review the uploaded documents before the account is verified.
            $session->documents()->each(function (VerificationDocument $document): void {
                $document->fields()->delete();
                $document->qrResults()->delete();
            });
            $this->manualReview($session, ['SERVER_SIDE_IDENTITY_VERIFICATION_REQUIRED']);
            $session->update(['status' => 'MANUAL_REVIEW_REQUIRED']);
            $session->user->update([
                'mykad_verification_status' => 'MANUAL_REVIEW_REQUIRED',
                'mykad_submitted_at' => now(),
                'mykad_verified_at' => null,
                'mykad_verification_session_id' => $session->id,
                'mykad_review_reason' => 'SERVER_SIDE_IDENTITY_VERIFICATION_REQUIRED',
            ]);
        });

        return $session->fresh(['documents.fields', 'documents.qrResults']);
    }

    public function verify(VerificationSession $session): VerificationSession
    {
        if ($session->status === 'MANUAL_REVIEW_REQUIRED' || $session->manualReview()->exists()) {
            return $session->fresh(['documents.fields', 'documents.qrResults', 'comparison', 'manualReview']);
        }

        $session->load('documents.fields', 'documents.qrResults', 'user.oku');
        $required = collect(['mykad_front', 'mykad_back']);
        $documents = $session->documents->keyBy('document_type');
        $missing = $required->reject(fn ($type) => $documents->get($type)?->quality_status === 'ACCEPTED')->values();
        if ($missing->isNotEmpty()) {
            $session->update(['status' => 'IMAGE_REJECTED']);
            $this->manualReview($session, $missing->map(fn ($type) => strtoupper($type).'_MISSING')->all());

            return $session->fresh();
        }

        $frontHash = data_get($documents->get('mykad_front')?->processing_metadata, 'sha256');
        $backHash = data_get($documents->get('mykad_back')?->processing_metadata, 'sha256');
        if ($frontHash && hash_equals($frontHash, (string) $backHash)) {
            $session->update(['status' => 'IMAGE_REJECTED']);
            $this->manualReview($session, ['DUPLICATE_CARD_IMAGE']);

            return $session->fresh();
        }

        $mykad = $this->fields($documents->get('mykad_front'));
        $okuDocument = $documents->get('oku_front');
        $oku = $okuDocument ? $this->fields($okuDocument) : [
            'name' => ['value' => $session->user->oku?->name, 'confidence' => 1.0],
            'nric' => ['value' => $session->user->oku?->ic_number, 'confidence' => 1.0],
        ];

        $result = $this->comparison->compare($oku, $mykad);
        $edited = $session->documents->flatMap->fields->contains('source', 'USER_EDITED');
        if ($edited) {
            $result['result'] = 'MANUAL_REVIEW_REQUIRED';
            $result['reasons'][] = 'USER_EDITED_OCR_DATA';
        }

        IdentityComparison::query()->updateOrCreate(['session_id' => $session->id], [
            'nric_match' => $result['nricMatch'],
            'name_match' => $result['nameMatch'],
            'name_similarity' => $result['nameSimilarity'],
            'result' => $result['result'],
            'reason_codes' => array_values(array_unique($result['reasons'])),
            'normalised_values' => $result['normalisedValues'],
        ]);

        $qrStatus = $session->documents->flatMap->qrResults->first()?->provider_status;
        $status = $result['result'];
        $reasons = $result['reasons'];
        if ($documents->has('oku_back') && ! $qrStatus) {
            $reasons[] = 'QR_NOT_DETECTED';
            if ($status === 'VERIFIED_LOCALLY_ONLY') {
                $status = 'MANUAL_REVIEW_REQUIRED';
            }
        }
        if ($qrStatus === 'INVALID_QR') {
            $reasons[] = 'INVALID_QR';
            $status = 'INVALID_QR';
        }
        if ($qrStatus && $qrStatus !== 'VERIFIED_BY_PROVIDER' && $status === 'VERIFIED_LOCALLY_ONLY') {
            $reasons[] = $qrStatus;
            $status = 'MANUAL_REVIEW_REQUIRED';
        }
        if (in_array($status, ['MANUAL_REVIEW_REQUIRED', 'INVALID_QR'], true)) {
            $this->manualReview($session, $reasons);
        }

        $session->update(['status' => $status]);
        $session->user->update([
            'mykad_verification_status' => $status,
            'mykad_submitted_at' => now(),
            'mykad_verified_at' => $status === 'VERIFIED' ? now() : null,
            'mykad_verification_session_id' => $session->id,
            'mykad_review_reason' => $reasons[0] ?? null,
            'mykad_resubmission_required' => false,
        ]);

        return $session->fresh(['documents.fields', 'documents.qrResults', 'comparison', 'manualReview']);
    }

    public function manualReview(VerificationSession $session, array $reasons): ManualReview
    {
        return ManualReview::query()->updateOrCreate(['session_id' => $session->id], [
            'status' => 'PENDING',
            'reason_codes' => array_values(array_unique($reasons ?: ['MANUAL_REVIEW_REQUIRED'])),
        ]);
    }

    private function fields(?VerificationDocument $document): array
    {
        if (! $document) {
            return [];
        }

        return $document->fields->mapWithKeys(fn ($field) => [
            $field->field_name => ['value' => $field->encrypted_value, 'confidence' => $field->confidence],
        ])->all();
    }
}
