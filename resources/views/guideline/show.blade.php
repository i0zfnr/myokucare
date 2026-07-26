@php
    $roleTranslation = [
        'oku_user' => 'oku_user',
        'employer' => 'employer',
        'jkm_officer' => 'jkm_officer',
    ];
    $stepGroups = [
        'oku_user' => ['key' => 'oku', 'count' => 7],
        'employer' => ['key' => 'employer', 'count' => 6],
        'jkm_officer' => ['key' => 'officer', 'count' => 6],
    ];
    $selectedSteps = $stepGroups[$role];
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" data-default-font-scale="100" data-default-high-contrast="0">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-head')
    <title>{{ __('guideline.page_title') }} — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/css/guideline.css','resources/js/app.js'])
</head>
<body class="guideline-page {{ $isOnboarding ? 'guideline-onboarding-page' : '' }}">
<a class="skip-link" href="#guideline-main">{{ __('accessibility.skip') }}</a>

<div
    class="guideline-root"
    data-guideline
    data-onboarding="{{ $isOnboarding ? '1' : '0' }}"
    data-replay="{{ $isReplay ? '1' : '0' }}"
    data-version="{{ $version }}"
    data-already-completed="{{ auth()->user()?->has_completed_guideline ? '1' : '0' }}"
    data-track-url="{{ route('guideline.track') }}"
    data-next-url="{{ $nextUrl }}"
>
    <header class="guideline-header">
        <a class="guideline-brand" href="{{ route('welcome') }}" aria-label="MyOKUcare">
            <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <span><strong>MyOKUcare</strong><small>{{ __('guideline.nav') }}</small></span>
        </a>
        <div class="guideline-header-actions">
            <div class="guideline-display-tools" role="toolbar" aria-label="{{ __('accessibility.display_settings') }}">
                <button type="button" data-font-action="decrease" aria-label="{{ __('accessibility.decrease_text') }}">A−</button>
                <button type="button" data-font-action="increase" aria-label="{{ __('accessibility.increase_text') }}">A+</button>
                <button type="button" data-contrast-toggle aria-label="{{ __('accessibility.high_contrast') }}" aria-pressed="false">◐</button>
            </div>
            <a class="btn guideline-home-link" href="{{ route('welcome') }}">← {{ __('nav.main') }}</a>
            <a class="btn guideline-account-link" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                {{ auth()->check() ? __('guideline.dashboard') : __('login') }}
            </a>
        </div>
    </header>

    <main id="guideline-main" tabindex="-1">
        @if($isOnboarding)
            <section class="onboarding-shell" aria-labelledby="onboarding-title">
                <div class="onboarding-progress" aria-hidden="true">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i === 1 ? 'active' : '' }}" data-slide-dot="{{ $i }}"></span>
                    @endfor
                </div>
                <p class="onboarding-progress-text" data-slide-progress aria-live="polite">{{ __('guideline.slide_progress', ['current' => 1, 'total' => 5]) }}</p>

                <div class="onboarding-slides" id="onboarding-title">
                    @for($i = 1; $i <= 5; $i++)
                        <article class="onboarding-slide" data-slide="{{ $i }}" @if($i !== 1) hidden @endif>
                            <div class="onboarding-illustration illustration-{{ $i }}" aria-hidden="true">
                                <span><x-dashboard-icon :name="match($i) { 1 => 'dashboard', 2 => 'id-card', 3 => 'briefcase', 4 => 'audit', default => 'add-record' }"/></span>
                            </div>
                            <p class="eyebrow">MyOKUcare</p>
                            <h1 tabindex="-1">{{ __("guideline.onboarding.{$i}_title") }}</h1>
                            <p>{{ __("guideline.onboarding.{$i}_text") }}</p>

                            @if($i === 4)
                                <div class="guideline-language-grid" aria-label="{{ __('guideline.language_title') }}">
                                    @foreach($languages as $code => $label)
                                        <form method="post" action="{{ route('guideline.language') }}" data-guideline-language>
                                            @csrf
                                            <input type="hidden" name="preferred_language" value="{{ $code }}">
                                            <input type="hidden" name="device_type" value="PWA">
                                            <button type="submit" class="{{ $currentLanguage === $code ? 'active' : '' }}" @if($currentLanguage === $code) aria-current="true" @endif>{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endfor
                </div>

                <div class="onboarding-actions">
                    <button class="btn onboarding-skip" type="button" data-guideline-skip>{{ __('guideline.skip') }}</button>
                    <div>
                        <button class="btn onboarding-previous" type="button" data-slide-previous hidden>{{ __('guideline.previous') }}</button>
                        <button class="btn btn-primary onboarding-next" type="button" data-slide-next>{{ __('guideline.next') }} →</button>
                        <button class="btn btn-primary onboarding-finish" type="button" data-guideline-complete hidden>{{ __('guideline.get_started') }}</button>
                    </div>
                </div>
            </section>
        @else
            <section class="guideline-hero" aria-labelledby="guideline-heading">
                <div>
                    <p class="eyebrow">{{ __('guideline.eyebrow') }}</p>
                    <h1 id="guideline-heading">{{ __('guideline.welcome_title') }}</h1>
                    <p>{{ __('guideline.welcome_text') }}</p>
                </div>
                <div class="guideline-language-panel">
                    <strong>{{ __('guideline.language_title') }}</strong>
                    <p>{{ __('guideline.language_text') }}</p>
                    <div class="guideline-language-grid">
                        @foreach($languages as $code => $label)
                            <form method="post" action="{{ route('guideline.language') }}" data-guideline-language>
                                @csrf
                                <input type="hidden" name="preferred_language" value="{{ $code }}">
                                <input type="hidden" name="device_type" value="WEB">
                                <button type="submit" class="{{ $currentLanguage === $code ? 'active' : '' }}" @if($currentLanguage === $code) aria-current="true" @endif>{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="guideline-section" aria-labelledby="choose-role-heading">
                <div class="guideline-section-heading">
                    <p class="eyebrow">01</p>
                    <h2 id="choose-role-heading">{{ __('guideline.choose_role') }}</h2>
                    <p>{{ __('guideline.choose_role_text') }}</p>
                </div>
                <div class="guideline-role-grid">
                    @foreach($roles as $roleOption)
                        <a href="{{ route('guideline.show', ['role' => $roleOption, 'replay' => $isReplay ? 1 : null]) }}" class="guideline-role-card {{ $role === $roleOption ? 'active' : '' }}" @if($role === $roleOption) aria-current="true" @endif>
                            <span class="guideline-role-icon" aria-hidden="true"><x-dashboard-icon :name="match($roleOption) { 'oku_user' => 'profile', 'employer' => 'briefcase', default => 'government' }"/></span>
                            <strong>{{ __("guideline.role.{$roleTranslation[$roleOption]}") }}</strong>
                            <small>{{ __("guideline.role.{$roleTranslation[$roleOption]}_text") }}</small>
                            <b aria-hidden="true">→</b>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="guideline-section guideline-steps-section" aria-labelledby="role-steps-heading">
                <div class="guideline-section-heading">
                    <p class="eyebrow">02</p>
                    <h2 id="role-steps-heading">{{ __('guideline.steps_title') }}: {{ __("guideline.role.{$roleTranslation[$role]}") }}</h2>
                    <p>{{ __('guideline.steps_intro') }}</p>
                </div>
                <ol class="guideline-steps">
                    @for($i = 1; $i <= $selectedSteps['count']; $i++)
                        <li>
                            <span aria-hidden="true">{{ $i }}</span>
                            <div>
                                <strong>{{ __("guideline.{$selectedSteps['key']}.{$i}_title") }}</strong>
                                <p>{{ __("guideline.{$selectedSteps['key']}.{$i}_text") }}</p>
                            </div>
                        </li>
                    @endfor
                </ol>
            </section>

            <section class="guideline-support" aria-labelledby="guideline-support-heading">
                <div>
                    <p class="eyebrow">03</p>
                    <h2 id="guideline-support-heading">{{ __('guideline.support_title') }}</h2>
                    <p>{{ __('guideline.support_text') }}</p>
                </div>
                <div class="guideline-support-actions">
                    <a class="btn" href="{{ route('welcome') }}#faq">{{ __('guideline.view_faq') }}</a>
                    <button class="btn btn-primary" type="button" data-guideline-complete>{{ __('guideline.complete') }}</button>
                </div>
            </section>
        @endif
    </main>
</div>
</body>
</html>
