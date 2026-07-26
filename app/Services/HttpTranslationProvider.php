<?php

namespace App\Services;

use App\Contracts\TranslationProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HttpTranslationProvider implements TranslationProvider
{
    public function available(): bool
    {
        return filled(config('translation.endpoint'));
    }

    public function detectLanguage(string $text): array
    {
        if (preg_match('/\p{Han}/u', $text)) {
            return ['language' => 'ZH_CN', 'confidence' => 0.99];
        }
        if (! $this->available()) {
            return ['language' => 'EN', 'confidence' => 0.50];
        }
        $response = Http::timeout(10)->acceptJson()->withToken(config('translation.api_key'))
            ->post(rtrim(config('translation.endpoint'), '/').'/detect', ['q' => $text])
            ->throw()->json();

        return [
            'language' => $this->normalise($response[0]['language'] ?? 'EN'),
            'confidence' => (float) ($response[0]['confidence'] ?? 0),
        ];
    }

    public function translate(string $text, string $sourceLanguage, string $targetLanguage): array
    {
        if (! $this->available()) {
            throw new RuntimeException('TRANSLATION_PROVIDER_UNAVAILABLE');
        }
        $response = Http::timeout(15)->acceptJson()->withToken(config('translation.api_key'))
            ->post(rtrim(config('translation.endpoint'), '/').'/translate', [
                'q' => $text,
                'source' => $this->providerCode($sourceLanguage),
                'target' => $this->providerCode($targetLanguage),
                'format' => 'text',
            ])->throw()->json();

        return ['text' => (string) ($response['translatedText'] ?? ''), 'confidence' => (float) ($response['confidence'] ?? 0.90)];
    }

    private function normalise(string $language): string
    {
        return match (strtolower($language)) {
            'ms', 'ms-my', 'bm' => 'BM',
            'zh', 'zh-cn', 'zh_hans' => 'ZH_CN',
            default => 'EN',
        };
    }

    private function providerCode(string $language): string
    {
        return ['BM' => 'ms', 'EN' => 'en', 'ZH_CN' => 'zh-CN'][$language] ?? throw new RuntimeException('UNSUPPORTED_LANGUAGE');
    }
}
