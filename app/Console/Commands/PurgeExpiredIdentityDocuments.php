<?php

namespace App\Console\Commands;

use App\Models\VerificationDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredIdentityDocuments extends Command
{
    protected $signature = 'identity:purge-expired';

    protected $description = 'Delete identity images after the configured retention period';

    public function handle(): int
    {
        $cutoff = now()->subDays(config('identity_verification.retention_days'));
        VerificationDocument::query()->where('created_at', '<', $cutoff)->chunkById(100, function ($documents): void {
            foreach ($documents as $document) {
                Storage::disk('local')->delete([$document->original_file_path, $document->processed_file_path]);
                $document->delete();
            }
        });

        return self::SUCCESS;
    }
}
