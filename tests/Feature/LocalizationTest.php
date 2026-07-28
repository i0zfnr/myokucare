<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_all_language_catalogues_have_identical_keys(): void
    {
        $catalogues = collect(['bm', 'en', 'zh-CN'])->mapWithKeys(
            fn (string $locale) => [$locale => json_decode(
                file_get_contents(lang_path($locale.'.json')),
                true,
                512,
                JSON_THROW_ON_ERROR,
            )]
        );

        $expected = collect($catalogues['bm'])->keys()->sort()->values()->all();

        foreach ($catalogues as $locale => $catalogue) {
            $this->assertSame(
                $expected,
                collect($catalogue)->keys()->sort()->values()->all(),
                "The {$locale} catalogue does not match the Bahasa Melayu catalogue.",
            );
            $this->assertNotContains('', $catalogue, "The {$locale} catalogue contains an empty translation.");
        }
    }

    #[DataProvider('localeProvider')]
    public function test_core_interface_strings_are_available_in_every_locale(string $locale): void
    {
        app()->setLocale($locale);

        foreach (['nav.dashboard', 'language.title', 'export.title', 'guideline.page_title', 'js.live_data'] as $key) {
            $this->assertNotSame($key, __($key), "Missing {$locale} translation for {$key}.");
        }
    }

    public static function localeProvider(): array
    {
        return [['bm'], ['en'], ['zh-CN']];
    }
}
