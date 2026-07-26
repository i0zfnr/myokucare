<?php

namespace App\Console\Commands;

use App\Models\ExportAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredExports extends Command
{
    protected $signature = 'exports:purge-expired';

    protected $description = 'Delete expired private export files';

    public function handle(): int
    {
        $count = 0;
        ExportAuditLog::query()->where('expires_at', '<=', now())->whereNotIn('status', ['DELETED', 'EXPIRED'])
            ->chunkById(100, function ($exports) use (&$count): void {
                foreach ($exports as $export) {
                    if ($export->generated_file_path) {
                        Storage::disk('local')->delete($export->generated_file_path);
                    }
                    $export->update(['status' => 'EXPIRED', 'generated_file_path' => null]);
                    $count++;
                }
            }, 'id');
        $this->info("Purged {$count} expired export(s).");

        return self::SUCCESS;
    }
}
