<?php

namespace Tests\Feature;

use App\Contracts\OkuVerificationProvider;
use App\Models\ExtractedField;
use App\Models\Oku;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Models\VerificationSession;
use App\Services\Identity\IdentityComparisonService;
use App\Services\Identity\IdentityNormalizer;
use App\Services\Identity\QrPayloadService;
use App\Services\Identity\VerificationWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_incomplete_oku_user_is_redirected_and_verified_user_can_continue(): void
    {
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('identity-verification.show'));
        $this->getJson(route('dashboard.statistics'))
            ->assertStatus(403)
            ->assertJsonPath('code', 'MYKAD_VERIFICATION_REQUIRED');
        $this->get(route('identity-verification.show'))->assertOk()->assertSeeText('Sahkan Kad OKU dan MyKad anda');

        $user->update(['mykad_verification_status' => 'VERIFIED']);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_session_requires_consent(): void
    {
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $this->actingAs($user)->postJson(route('identity-verification.session.create'), [])
            ->assertUnprocessable();
    }

    public function test_session_is_created_with_consent_and_owned_by_user(): void
    {
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $response = $this->actingAs($user)->postJson(route('identity-verification.session.create'), ['consent' => true])
            ->assertCreated()->assertJsonStructure(['sessionId', 'status']);
        $session = VerificationSession::query()->latest()->firstOrFail();
        $this->assertNotNull($session->consent_accepted_at);

        [$other] = $this->okuUser('NOT_SUBMITTED');
        $this->actingAs($other)->getJson(route('identity-verification.session.show', $session))->assertForbidden();
    }

    public function test_upload_rejects_pdf_files(): void
    {
        Storage::fake('local');
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $session = $this->verificationSession($user);
        $route = route('identity-verification.upload', $session);

        $this->actingAs($user)->post($route, [
            'document_type' => 'mykad_front',
            'image' => UploadedFile::fake()->create('mykad.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_upload_rejects_unsupported_files(): void
    {
        Storage::fake('local');
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $session = $this->verificationSession($user);
        $this->actingAs($user)->post(route('identity-verification.upload', $session), [
            'document_type' => 'mykad_front',
            'image' => UploadedFile::fake()->create('mykad.txt', 20, 'text/plain'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_upload_rejects_invalid_image_bytes(): void
    {
        Storage::fake('local');
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $session = $this->verificationSession($user);
        $this->actingAs($user)->post(route('identity-verification.upload', $session), [
            'document_type' => 'mykad_front',
            'image' => UploadedFile::fake()->create('fake.jpg', 20, 'image/jpeg'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_upload_rejects_oversized_files(): void
    {
        Storage::fake('local');
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $session = $this->verificationSession($user);
        $this->actingAs($user)->post(route('identity-verification.upload', $session), [
            'document_type' => 'mykad_front',
            'image' => UploadedFile::fake()->create('large.jpg', config('identity_verification.max_upload_kb') + 1, 'image/jpeg'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_low_quality_image_is_rejected_with_specific_issue_codes(): void
    {
        Storage::fake('local');
        [$user] = $this->okuUser('NOT_SUBMITTED');
        $session = $this->verificationSession($user);
        $image = UploadedFile::fake()->image('dark.png', 400, 250);

        $response = $this->actingAs($user)->post(route('identity-verification.upload', $session), [
            'document_type' => 'mykad_front', 'image' => $image,
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->assertNotEmpty($response->json('issues'));
        $this->assertContains('IMAGE_LOW_RESOLUTION', $response->json('issues'));
        $this->assertContains('CARD_NOT_DETECTED', $response->json('issues'));
        $document = VerificationDocument::query()->firstOrFail();
        $this->assertStringEndsWith('.enc', $document->original_file_path);
        $this->assertStringEndsWith('.enc', $document->processed_file_path);
        Storage::disk('local')->assertExists([$document->original_file_path, $document->processed_file_path]);
    }

    public function test_normalisation_and_name_similarity_handle_formatting_and_minor_ocr_errors(): void
    {
        $normalizer = app(IdentityNormalizer::class);
        $comparison = app(IdentityComparisonService::class);
        $this->assertSame('001122033344', $normalizer->nric('001122-03-3344'));
        $this->assertSame('SITI NUR BINTI ALI', $normalizer->name('  Siti  Nur, Binti Ali. '));

        $spacing = $comparison->compare(
            $this->identity('SITI  NUR BINTI ALI', '001122-03-3344'),
            $this->identity('siti nur binti ali', '001122033344'),
        );
        $this->assertSame('VERIFIED_LOCALLY_ONLY', $spacing['result']);

        $minorError = $comparison->compare(
            $this->identity('SITI NUR BINTI ALI', '001122033344'),
            $this->identity('SITI NUR BINTI AL1', '001122033344'),
        );
        $this->assertTrue($minorError['nameMatch']);
    }

    public function test_comparison_never_approves_name_alone_and_routes_low_confidence_or_name_mismatch_to_review(): void
    {
        $service = app(IdentityComparisonService::class);
        $nricMismatch = $service->compare($this->identity('SITI NUR', '001122033344'), $this->identity('SITI NUR', '991122033344'));
        $this->assertSame('DETAILS_MISMATCH', $nricMismatch['result']);

        $nameMismatch = $service->compare($this->identity('SITI NUR', '001122033344'), $this->identity('AHMAD ALI', '001122033344'));
        $this->assertSame('MANUAL_REVIEW_REQUIRED', $nameMismatch['result']);

        $lowConfidence = $service->compare($this->identity('SITI NUR', '001122033344', .5), $this->identity('SITI NUR', '001122033344', .5));
        $this->assertSame('MANUAL_REVIEW_REQUIRED', $lowConfidence['result']);
    }

    public function test_qr_types_invalid_qr_and_unavailable_provider_are_explicit(): void
    {
        $qr = app(QrPayloadService::class);
        $this->assertSame('INVALID', $qr->classify(''));
        $this->assertSame('URL', $qr->classify('https://example.test/verify/123'));
        $this->assertSame('STRUCTURED_JSON', $qr->classify('{"registration":"LD123"}'));
        $this->assertSame('REGISTRATION_IDENTIFIER', $qr->classify('LD1234567890'));
        $this->assertSame('UNVERIFIED_EXTERNAL_DATA', app(OkuVerificationProvider::class)->verifyQrPayload('data')['status']);
    }

    public function test_matching_documents_verify_locally_while_mismatch_and_low_confidence_create_manual_review(): void
    {
        [$user, $oku] = $this->okuUser('SUBMISSION_IN_PROGRESS');
        $session = $this->verificationSession($user);
        $front = $this->acceptedDocument($session, 'mykad_front', 'front-hash');
        $this->acceptedDocument($session, 'mykad_back', 'back-hash');
        $this->field($front, 'name', $oku->name, .98);
        $this->field($front, 'nric', $oku->ic_number, .98);

        app(VerificationWorkflowService::class)->verify($session);
        $this->assertSame('VERIFIED_LOCALLY_ONLY', $session->fresh()->status);
        $this->assertFalse($user->fresh()->hasVerifiedMyKad());

        [$otherUser] = $this->okuUser('SUBMISSION_IN_PROGRESS');
        $otherSession = $this->verificationSession($otherUser);
        $otherFront = $this->acceptedDocument($otherSession, 'mykad_front', 'front-2');
        $this->acceptedDocument($otherSession, 'mykad_back', 'back-2');
        $this->field($otherFront, 'name', 'NAMA BERBEZA', .99);
        $this->field($otherFront, 'nric', $otherUser->oku->ic_number, .99);
        app(VerificationWorkflowService::class)->verify($otherSession);
        $this->assertSame('MANUAL_REVIEW_REQUIRED', $otherSession->fresh()->status);
        $this->assertDatabaseHas('manual_reviews', ['session_id' => $otherSession->id, 'status' => 'PENDING']);
    }

    public function test_duplicate_sides_and_expired_sessions_do_not_verify(): void
    {
        [$user] = $this->okuUser('SUBMISSION_IN_PROGRESS');
        $session = $this->verificationSession($user);
        $this->acceptedDocument($session, 'mykad_front', 'same');
        $this->acceptedDocument($session, 'mykad_back', 'same');
        app(VerificationWorkflowService::class)->verify($session);
        $this->assertSame('IMAGE_REJECTED', $session->fresh()->status);
        $this->assertContains('DUPLICATE_CARD_IMAGE', $session->manualReview->reason_codes);

        $expired = $this->verificationSession($user, now()->subMinute());
        $this->actingAs($user)->postJson(route('identity-verification.verify', $expired))->assertGone();
    }

    private function identity(string $name, string $nric, float $confidence = .95): array
    {
        return ['name' => compact('name', 'confidence') + ['value' => $name], 'nric' => ['value' => $nric, 'confidence' => $confidence]];
    }

    private function okuUser(string $status): array
    {
        $oku = Oku::query()->create([
            'name' => 'SITI NUR BINTI ALI', 'ic_number' => fake()->unique()->numerify('######-##-####'),
            'gender' => 'Perempuan', 'age' => 30, 'marital_status' => 'Bujang', 'address' => 'Besut',
            'education_level' => 'SPM / SPMV', 'oku_card_number' => fake()->unique()->bothify('OKU########'),
            'oku_category' => 'Fizikal', 'employment_status' => 'Tidak Bekerja', 'phone_number' => '0123456789',
            'profile_reviewed_at' => now(),
        ]);
        $user = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id, 'mykad_verification_status' => $status]);

        return [$user, $oku];
    }

    private function verificationSession(User $user, $expiry = null): VerificationSession
    {
        return $user->verificationSessions()->create([
            'status' => 'SUBMISSION_IN_PROGRESS', 'consent_accepted_at' => now(),
            'expires_at' => $expiry ?? now()->addHour(),
        ]);
    }

    private function acceptedDocument(VerificationSession $session, string $type, string $hash): VerificationDocument
    {
        return $session->documents()->create([
            'document_type' => $type, 'original_file_path' => "private/{$type}",
            'processed_file_path' => "private/processed-{$type}", 'quality_status' => 'ACCEPTED',
            'quality_score' => .95, 'processing_metadata' => ['sha256' => $hash],
        ]);
    }

    private function field(VerificationDocument $document, string $name, string $value, float $confidence): void
    {
        ExtractedField::query()->create([
            'document_id' => $document->id, 'field_name' => $name, 'encrypted_value' => $value,
            'masked_value' => $name === 'nric' ? '******-**-'.substr(preg_replace('/\D/', '', $value), -4) : null,
            'confidence' => $confidence, 'source' => 'OCR',
        ]);
    }
}
