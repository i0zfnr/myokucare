@extends('layout',['title'=>__('jobs.candidates_title')])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('jobs.inclusive_employment') }}</p><h2>{{ __('jobs.candidates_heading',['job'=>$job->title]) }}</h2><p>{{ $job->employer->company_name }} · {{ $job->location }}</p></div><a class="btn" href="{{ route('jobs.index') }}">{{ __('jobs.back_to_jobs') }}</a></div>
@if($errors->any())<div class="error form-error-summary" role="alert"><strong>{{ __('jobs.validation_heading') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="job-directory">
@forelse($candidates as $interest)
@php $shared=(bool)$interest->profile_shared_at; $oku=$interest->oku; @endphp
<article class="panel opportunity-card">
    <div class="panel-head"><div><p class="eyebrow">{{ __('jobs.application_number',['id'=>$interest->id]) }}</p><h3>{{ $shared ? $oku->name : __('jobs.profile_not_shared') }}</h3><p>{{ $interest->application_date?->format('d/m/Y') }} · {{ __("jobs.statuses.{$interest->status}") }}</p></div><span class="badge">{{ $shared ? __('jobs.consent_given') : __('jobs.no_consent') }}</span></div>
    @if($shared)
    <dl class="record-list"><div><dt>{{ __('jobs.oku_category') }}</dt><dd>{{ __("jobs.oku_categories.{$oku->oku_category}") }}</dd></div><div><dt>{{ __('jobs.education') }}</dt><dd>{{ $oku->education_level }}</dd></div><div><dt>{{ __('jobs.availability') }}</dt><dd>{{ __("jobs.availability_values.{$oku->availability_status}") }}</dd></div><div><dt>{{ __('jobs.skills') }}</dt><dd>{{ $oku->skills ?: __('jobs.not_provided') }}</dd></div><div><dt>{{ __('jobs.career_summary') }}</dt><dd>{{ $oku->career_summary ?: __('jobs.not_provided') }}</dd></div></dl>
    @if($oku->resume_path)<a class="btn" href="{{ route('jobs.candidates.resume',[$job,$interest]) }}">{{ __('jobs.download_resume') }}</a>@else<span class="badge">{{ __('jobs.no_resume') }}</span>@endif
    @else<p>{{ __('jobs.privacy_hidden') }}</p>@endif
    <form class="oku-record-form" method="post" action="{{ route('jobs.candidates.update',[$job,$interest]) }}">@csrf @method('PATCH')<div class="form-grid"><div class="form-group"><label for="status-{{ $interest->id }}">{{ __('jobs.candidate_status') }}</label><select class="select" id="status-{{ $interest->id }}" name="status" required>@foreach(['Interested','Applied','Shortlisted','Interviewed','Hired','Rejected'] as $value)<option value="{{ $value }}" @selected($interest->status===$value)>{{ __("jobs.statuses.$value") }}</option>@endforeach</select></div><div class="form-group"><label for="interview-date-{{ $interest->id }}">{{ __('jobs.interview_date') }}</label><input class="field" id="interview-date-{{ $interest->id }}" name="interview_date" type="date" value="{{ $interest->interview_date?->format('Y-m-d') }}"></div><div class="form-group full"><label for="notes-{{ $interest->id }}">{{ __('jobs.notes') }}</label><textarea class="textarea" id="notes-{{ $interest->id }}" name="notes" rows="3">{{ $interest->notes }}</textarea></div></div><button class="btn btn-primary" type="submit">{{ __('jobs.update_status') }}</button></form>
</article>
@empty<section class="panel job-empty"><h3>{{ __('jobs.no_candidates') }}</h3><p>{{ __('jobs.no_candidates_help') }}</p></section>@endforelse
</div><div class="pagination">{{ $candidates->links() }}</div>
@endsection
