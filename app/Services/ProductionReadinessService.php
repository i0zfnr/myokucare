<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class ProductionReadinessService
{
    public function checks(): array
    {
        return [
            $this->check('environment', app()->environment('production'), 'APP_ENV mesti production.'),
            $this->check('debug', ! config('app.debug'), 'APP_DEBUG mesti false.'),
            $this->check('https', str_starts_with((string) config('app.url'), 'https://'), 'APP_URL mesti menggunakan HTTPS.'),
            $this->check('app_key', filled(config('app.key')) && strlen((string) config('app.key')) >= 32, 'APP_KEY kukuh diperlukan.'),
            $this->check('database', config('database.default') !== 'sqlite', 'Gunakan MySQL/MariaDB, bukan SQLite, untuk pengeluaran.'),
            $this->check('session_driver', config('session.driver') === 'database', 'SESSION_DRIVER mesti database.'),
            $this->check('session_encryption', config('session.encrypt') === true, 'SESSION_ENCRYPT mesti true.'),
            $this->check('secure_cookie', config('session.secure') === true, 'SESSION_SECURE_COOKIE mesti true.'),
            $this->check('http_only_cookie', config('session.http_only') === true, 'SESSION_HTTP_ONLY mesti true.'),
            $this->check('same_site_cookie', in_array(config('session.same_site'), ['lax', 'strict'], true), 'SESSION_SAME_SITE mesti lax atau strict.'),
            $this->check('session_lifetime', in_array((int) config('session.lifetime'), range(30, 60), true), 'SESSION_LIFETIME mestilah antara 30 hingga 60 minit.'),
            $this->check('mail', ! in_array(config('mail.default'), ['log', 'array'], true), 'Konfigurasikan penyedia e-mel sebenar.'),
            $this->check('queue', config('queue.default') !== 'sync', 'Gunakan queue worker berterusan.'),
            $this->check('cache', config('cache.default') !== 'array', 'Gunakan cache berterusan.'),
            $this->check('daily_logs', in_array('daily', config('logging.channels.stack.channels', []), true), 'LOG_STACK mesti mengandungi daily.'),
            $this->check('private_storage', config('filesystems.disks.local.root') === storage_path('app/private'), 'Disk local mesti menunjuk ke storage/app/private.'),
            $this->check('notifications_table', $this->tableExists('notifications'), 'Jalankan migrasi jadual notifications.'),
            $this->check('push_subscriptions_table', $this->tableExists('push_subscriptions'), 'Jalankan migrasi jadual push_subscriptions.'),
            $this->check('firebase_push', $this->firebaseReady(), 'Lengkapkan konfigurasi Firebase FCM dan fail service account.'),
            $this->check('storage_writable', is_writable(storage_path()), 'Direktori storage mesti boleh ditulis.'),
            $this->check('cache_writable', is_writable(base_path('bootstrap/cache')), 'Direktori bootstrap/cache mesti boleh ditulis.'),
        ];
    }

    public function failures(): array
    {
        return array_values(array_filter($this->checks(), fn (array $check) => ! $check['passed']));
    }

    private function check(string $name, bool $passed, string $message): array
    {
        return compact('name', 'passed', 'message');
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function firebaseReady(): bool
    {
        if (! config('services.firebase.enabled')) return false;
        $firebase = config('services.firebase');

        return filled($firebase['project_id'] ?? null)
            && filled($firebase['vapid_public_key'] ?? null)
            && filled($firebase['web']['apiKey'] ?? null)
            && filled($firebase['web']['messagingSenderId'] ?? null)
            && filled($firebase['web']['appId'] ?? null)
            && is_file((string) ($firebase['service_account_path'] ?? ''));
    }
}
