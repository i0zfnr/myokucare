@extends('layout', ['title' => __('language.title')])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('settings') }}</p><h2>{{ __('language.title') }}</h2><p>{{ __('language.description') }}</p></div></div>
<form class="panel form-section" method="post" action="{{ route('language-settings.update') }}">@csrf @method('PUT')
<div class="form-grid">
<div class="form-group"><label>{{ __('language.current') }}</label><p class="field" aria-live="polite">{{ $languages[$currentLanguage] ?? $languages['BM'] }}</p></div>
<div class="form-group"><label for="preferred-language">{{ __('language.change') }}</label><select class="select" id="preferred-language" name="preferred_language" required>@foreach($languages as $value=>$label)<option value="{{ $value }}" @selected($currentLanguage===$value)>{{ $label }}</option>@endforeach</select></div>
</div>
<div class="form-actions"><button class="btn btn-primary" type="submit">{{ __('language.save') }}</button></div>
</form>
<section class="panel form-section" aria-labelledby="guideline-help-heading">
<div class="form-section-head"><span aria-hidden="true">?</span><div><h3 id="guideline-help-heading">{{ __('guideline.nav') }}</h3><p>{{ __('guideline.support_text') }}</p></div></div>
<a class="btn" href="{{ route('guideline.show', ['replay' => 1]) }}">{{ __('guideline.replay') }}</a>
</section>
@endsection
