@extends('layout', ['title' => __('guideline.page_title')])

@section('content')
@vite('resources/css/guideline.css')
@php
    $roleTranslation = ['oku_user' => 'oku_user', 'employer' => 'employer', 'jkm_officer' => 'jkm_officer'];
    $stepGroups = [
        'oku_user' => ['key' => 'oku', 'count' => 7],
        'employer' => ['key' => 'employer', 'count' => 6],
        'jkm_officer' => ['key' => 'officer', 'count' => 6],
    ];
    $selectedSteps = $stepGroups[$role];
@endphp

<div class="guideline-authenticated" data-guideline data-onboarding="0" data-replay="{{ $isReplay ? '1' : '0' }}" data-version="{{ $version }}" data-already-completed="{{ auth()->user()->has_completed_guideline ? '1' : '0' }}" data-track-url="{{ route('guideline.track') }}" data-next-url="{{ route('dashboard') }}">
    <div class="page-head guideline-app-head">
        <div><p class="eyebrow">{{ __('guideline.eyebrow') }}</p><h2>{{ __('guideline.welcome_title') }}</h2><p>{{ __('guideline.welcome_text') }}</p></div>
        <div class="page-actions"><a class="btn" href="{{ route('welcome') }}">← {{ __('nav.main') }}</a><a class="btn btn-primary" href="{{ route('dashboard') }}">{{ __('guideline.dashboard') }}</a></div>
    </div>

    <section class="panel guideline-app-language" aria-labelledby="guideline-language-heading">
        <div><h3 id="guideline-language-heading">{{ __('guideline.language_title') }}</h3><p>{{ __('guideline.language_text') }}</p></div>
        <div class="guideline-language-grid">
            @foreach($languages as $code => $label)
                <form method="post" action="{{ route('guideline.language') }}" data-guideline-language>@csrf<input type="hidden" name="preferred_language" value="{{ $code }}"><input type="hidden" name="device_type" value="WEB"><button type="submit" class="{{ $currentLanguage === $code ? 'active' : '' }}" @if($currentLanguage === $code) aria-current="true" @endif>{{ $label }}</button></form>
            @endforeach
        </div>
    </section>

    <section class="guideline-app-section" aria-labelledby="app-role-heading">
        <div class="guideline-app-section-head"><span aria-hidden="true">01</span><div><h3 id="app-role-heading">{{ __('guideline.choose_role') }}</h3><p>{{ __('guideline.choose_role_text') }}</p></div></div>
        <div class="guideline-role-grid">
            @foreach($roles as $roleOption)
                <a href="{{ route('guideline.show', ['role' => $roleOption, 'replay' => $isReplay ? 1 : null]) }}" class="guideline-role-card {{ $role === $roleOption ? 'active' : '' }}" @if($role === $roleOption) aria-current="true" @endif>
                    <span class="guideline-role-icon" aria-hidden="true"><x-dashboard-icon :name="match($roleOption) { 'oku_user' => 'profile', 'employer' => 'briefcase', default => 'government' }"/></span>
                    <strong>{{ __("guideline.role.{$roleTranslation[$roleOption]}") }}</strong><small>{{ __("guideline.role.{$roleTranslation[$roleOption]}_text") }}</small><b aria-hidden="true">→</b>
                </a>
            @endforeach
        </div>
    </section>

    <section class="guideline-app-section" aria-labelledby="app-steps-heading">
        <div class="guideline-app-section-head"><span aria-hidden="true">02</span><div><h3 id="app-steps-heading">{{ __('guideline.steps_title') }}: {{ __("guideline.role.{$roleTranslation[$role]}") }}</h3><p>{{ __('guideline.steps_intro') }}</p></div></div>
        <ol class="guideline-steps">
            @for($i = 1; $i <= $selectedSteps['count']; $i++)
                <li><span aria-hidden="true">{{ $i }}</span><div><strong>{{ __("guideline.{$selectedSteps['key']}.{$i}_title") }}</strong><p>{{ __("guideline.{$selectedSteps['key']}.{$i}_text") }}</p></div></li>
            @endfor
        </ol>
    </section>

    <section class="guideline-support guideline-app-support" aria-labelledby="app-support-heading">
        <div><p class="eyebrow">03</p><h2 id="app-support-heading">{{ __('guideline.support_title') }}</h2><p>{{ __('guideline.support_text') }}</p></div>
        <div class="guideline-support-actions"><a class="btn" href="{{ route('welcome') }}#faq">{{ __('guideline.view_faq') }}</a><a class="btn btn-primary" href="{{ route('dashboard') }}">{{ __('guideline.dashboard') }}</a></div>
    </section>
</div>
@endsection
