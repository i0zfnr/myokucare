@extends('layout', ['title' => 'Butiran Semakan Identiti'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Semakan Manual</p><h2>{{ $review->session->user->name }}</h2><p>ID sesi: {{ $review->session_id }}</p></div><a class="btn" href="{{ route('identity-reviews.index') }}">Kembali</a></div>
<div class="dashboard-grid">
<section class="panel" style="padding:20px"><h3>Dokumen</h3><div class="job-grid">@foreach($review->session->documents as $document)<article><strong>{{ str_replace('_',' ',strtoupper($document->document_type)) }}</strong><p>Skor kualiti: {{ number_format($document->quality_score * 100) }}%</p><a class="btn" target="_blank" rel="noopener" href="{{ route('identity-reviews.document', [$review, $document]) }}">Lihat imej</a></article>@endforeach</div></section>
<aside class="panel" style="padding:20px"><h3>Keputusan</h3><p>Sebab: {{ implode(', ', $review->reason_codes) }}</p><p>NRIC hanya dipaparkan dalam bentuk bertopeng pada paparan biasa.</p><form method="post" action="{{ route('identity-reviews.update', $review) }}">@csrf @method('PUT')<div class="form-group"><label for="status">Keputusan pegawai</label><select class="select" id="status" name="status" required>@foreach(['APPROVED','REJECTED','NEEDS_RESUBMISSION'] as $status)<option>{{ $status }}</option>@endforeach</select></div><button class="btn btn-primary" type="submit">Simpan Keputusan</button></form></aside>
</div>
@endsection
