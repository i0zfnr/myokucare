@extends('layout',['title'=>'Padanan Pekerjaan'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Pekerjaan Inklusif</p><h2>Cadangan untuk {{ $oku->name }}</h2><p>Peluang aktif yang sesuai dengan kategori {{ $oku->oku_category }}.</p></div><a class="btn" href="{{ route('oku.show',$oku) }}">← Kembali ke Profil</a></div>
<div class="job-grid">@forelse($matchingJobs as $job)<article class="panel job-card"><div class="panel-head"><div><h3>{{ $job->title }}</h3><p>{{ $job->employer->company_name }}</p></div><span class="badge">{{ $job->match_score }}% padan</span></div><p>{{ $job->description }}</p><p><strong>{{ $job->location }}</strong> · {{ $job->salary_range }}</p></article>@empty<div class="panel empty">Tiada pekerjaan aktif yang sepadan buat masa ini.</div>@endforelse</div>
@endsection
