<?php

namespace App\Services\Identity;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class SecureImageService
{
    private const MIME_EXTENSIONS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function validate(UploadedFile $file): array
    {
        if ($file->getClientMimeType() === 'application/pdf' || strtolower($file->getClientOriginalExtension()) === 'pdf') {
            throw ValidationException::withMessages(['image' => 'PDF_NOT_ALLOWED']);
        }
        if ($file->getSize() > config('identity_verification.max_upload_kb') * 1024) {
            throw ValidationException::withMessages(['image' => 'FILE_TOO_LARGE']);
        }

        $detectedMime = $file->getMimeType();
        if (! isset(self::MIME_EXTENSIONS[$detectedMime])) {
            throw ValidationException::withMessages(['image' => 'UNSUPPORTED_FILE_TYPE']);
        }
        if ($file->getClientMimeType() !== $detectedMime) {
            throw ValidationException::withMessages(['image' => 'FILE_SIGNATURE_MISMATCH']);
        }
        $dimensions = @getimagesize($file->getRealPath());
        if (! $dimensions) {
            throw ValidationException::withMessages(['image' => 'IMAGE_DECODE_FAILED']);
        }

        return ['mime' => $detectedMime, 'extension' => self::MIME_EXTENSIONS[$detectedMime], 'width' => $dimensions[0], 'height' => $dimensions[1]];
    }

    public function storeAndProcess(UploadedFile $file, string $sessionId, string $type): array
    {
        $validated = $this->validate($file);
        $directory = "identity-verification/{$sessionId}";
        $filename = bin2hex(random_bytes(24)).'.'.$validated['extension'];
        $original = "{$directory}/original-{$filename}";
        Storage::disk('local')->put($original, $this->sanitisedImageBytes($file, $validated['mime']));
        $processed = "{$directory}/processed-".pathinfo($filename, PATHINFO_FILENAME).'.jpg';

        $process = new Process([
            config('identity_verification.python_binary'),
            config('identity_verification.opencv_script'),
            Storage::disk('local')->path($original),
            Storage::disk('local')->path($processed),
        ]);
        $process->setTimeout(30)->run();
        if (! $process->isSuccessful()) {
            Storage::disk('local')->delete($original);
            throw ValidationException::withMessages(['image' => 'IMAGE_PROCESSING_FAILED']);
        }
        $metadata = json_decode($process->getOutput(), true);
        if (! is_array($metadata)) {
            Storage::disk('local')->delete([$original, $processed]);
            throw ValidationException::withMessages(['image' => 'IMAGE_PROCESSING_FAILED']);
        }

        $metadata['sha256'] = hash_file('sha256', $file->getRealPath());
        $metadata['processedMime'] = 'image/jpeg';

        return [
            'original' => $this->encryptStoredFile($original),
            'processed' => $this->encryptStoredFile($processed),
            'metadata' => $metadata,
        ];
    }

    public function decryptedBytes(string $path): string
    {
        return base64_decode(Crypt::decryptString(Storage::disk('local')->get($path)), true)
            ?: throw new \RuntimeException('Encrypted image could not be decoded.');
    }

    private function sanitisedImageBytes(UploadedFile $file, string $mime): string
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $image) {
            throw ValidationException::withMessages(['image' => 'IMAGE_DECODE_FAILED']);
        }

        ob_start();
        match ($mime) {
            'image/png' => imagepng($image, null, 8),
            'image/webp' => imagewebp($image, null, 92),
            default => imagejpeg($image, null, 94),
        };
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes ?: throw ValidationException::withMessages(['image' => 'IMAGE_DECODE_FAILED']);
    }

    private function encryptStoredFile(string $path): string
    {
        $encryptedPath = $path.'.enc';
        Storage::disk('local')->put(
            $encryptedPath,
            Crypt::encryptString(base64_encode(Storage::disk('local')->get($path))),
        );
        Storage::disk('local')->delete($path);

        return $encryptedPath;
    }
}
