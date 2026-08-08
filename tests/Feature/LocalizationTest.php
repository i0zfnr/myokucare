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

    public function test_every_literal_blade_translation_key_resolves_in_every_locale(): void
    {
        $keys = collect();
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) continue;
            preg_match_all("/(?:__|trans)\\(\\s*['\"]([^'\"]+)['\"]|@lang\\(\\s*['\"]([^'\"]+)['\"]/", file_get_contents($file->getPathname()), $matches);
            $keys->push(...array_filter(
                [...$matches[1], ...$matches[2]],
                fn (string $key) => $key !== '' && ! str_contains($key, '$') && ! str_ends_with($key, '.'),
            ));
        }

        $missing = [];
        foreach (['bm', 'en', 'zh-CN'] as $locale) {
            app()->setLocale($locale);
            foreach ($keys->unique() as $key) {
                if (__($key) === $key) $missing[] = "{$locale}:{$key}";
            }
        }

        $this->assertSame([], $missing, 'Missing Blade translations: '.implode(', ', $missing));
    }

    #[DataProvider('localeProvider')]
    public function test_job_workflow_dynamic_values_are_translated(string $locale): void
    {
        app()->setLocale($locale);

        $keys = [
            ...array_map(fn ($value) => "jobs.categories.{$value}", config('jobs.categories')),
            ...array_map(fn ($value) => "jobs.statuses.{$value}", ['Interested', 'Applied', 'Shortlisted', 'Interviewed', 'Hired', 'Rejected']),
            ...array_map(fn ($value) => "jobs.employment_types.{$value}", ['Sepenuh Masa', 'Separuh Masa', 'Kontrak', 'Sementara']),
            ...array_map(fn ($value) => "jobs.oku_categories.{$value}", ['Semua', 'Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan']),
        ];

        foreach ($keys as $key) {
            $this->assertNotSame($key, __($key), "Missing {$locale} translation for {$key}.");
        }
    }
}
