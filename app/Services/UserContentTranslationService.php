<?php

namespace App\Services;

use App\Contracts\TranslationProvider;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserSubmissionTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserContentTranslationService
{
    private const EXCLUDED_FIELDS = ['ic_number', 'nric', 'oku_card_number', 'registration_number', 'date', 'salary'];

    public function __construct(private TranslationProvider $provider) {}

    public function capture(User $user, Model $record, string $fieldName, ?string $text): ?UserSubmissionTranslation
    {
        if (blank($text) || in_array($fieldName, self::EXCLUDED_FIELDS, true)) {
            return null;
        }
        $detection = $this->provider->detectLanguage($text);
        $values = [
            'user_id' => $user->id,
            'original_text' => $text,
            'original_language' => $detection['language'],
            'translation_confidence' => $detection['confidence'],
            'provider_status' => 'PROVIDER_UNAVAILABLE',
        ];
        if ($this->provider->available()) {
            try {
                $values['translated_text_bm'] = $detection['language'] === 'BM' ? $text : $this->provider->translate($text, $detection['language'], 'BM')['text'];
                $values['translated_text_en'] = $detection['language'] === 'EN' ? $text : $this->provider->translate($text, $detection['language'], 'EN')['text'];
                $values['provider_status'] = 'TRANSLATED';
                $values['translated_at'] = now();
            } catch (\Throwable) {
                $values['provider_status'] = 'PROVIDER_UNAVAILABLE';
            }
        }
        $translation = UserSubmissionTranslation::query()->updateOrCreate([
            'translatable_type' => $record->getMorphClass(),
            'translatable_id' => $record->getKey(),
            'field_name' => $fieldName,
        ], $values);
        ActivityLog::query()->create([
            'actor_id' => $user->id,
            'subject_user_id' => $user->id,
            'action' => 'user_content_translation_updated',
            'changes' => ['translation_id' => $translation->id, 'field' => $fieldName, 'status' => $translation->provider_status],
        ]);

        return $translation;
    }

    public function matchingRecordIds(string $modelClass, string $term): Collection
    {
        $needle = mb_strtolower(trim($term));

        return UserSubmissionTranslation::query()
            ->where('translatable_type', (new $modelClass)->getMorphClass())
            ->get()
            ->filter(function (UserSubmissionTranslation $translation) use ($needle): bool {
                return collect([
                    $translation->original_text,
                    $translation->translated_text_bm,
                    $translation->translated_text_en,
                ])->filter()->contains(fn ($value) => str_contains(mb_strtolower($value), $needle));
            })
            ->pluck('translatable_id')
            ->unique()
            ->values();
    }
}
