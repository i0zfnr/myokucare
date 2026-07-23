@extends('layout',['title'=>'Peluang Kerja'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Pekerjaan Inklusif</p><h2>Senarai Peluang Kerja</h2><p>Jawatan tersedia daripada majikan berdaftar.</p></div></div>
<div class="job-grid">@forelse($jobs as $job)<article class="panel job-card"><div class="panel-head"><div><h3>{{ $job->title }}</h3><p>{{ $job->employer->company_name }}</p></div><span class="badge">{{ $job->oku_category_suitable }}</span></div><p>{{ \Illuminate\Support\Str::limit($job->description,130) }}</p><p><strong>{{ $job->location }}</strong> · {{ $job->salary_range }}</p></article>@empty<div class="panel empty">Tiada peluang kerja aktif.</div>@endforelse</div><div class="pagination">{{ $jobs->links() }}</div>
@endsection
