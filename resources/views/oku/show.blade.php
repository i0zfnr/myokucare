@extends('layout',['title'=>'Profil OKU'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Profil Individu</p><h2>Maklumat OKU</h2></div><div style="display:flex;gap:10px"><a class="btn" href="{{ route('oku.index') }}">← Senarai</a><a class="btn btn-primary" href="{{ route('oku.edit',$oku) }}">Kemaskini</a></div></div>
<section class="panel profile-hero"><span class="profile-avatar">{{ collect(explode(' ',$oku->name))->take(2)->map(fn($v)=>strtoupper(substr($v,0,1)))->implode('') }}</span><div><h2>{{ $oku->name }}</h2><p>{{ $oku->ic_number }} · {{ $oku->oku_card_number }}</p><div style="margin-top:10px"><span class="badge">{{ $oku->oku_category }}</span> <span class="badge">{{ $oku->employment_status }}</span></div></div></section>
<section class="panel staff-verification">
    <div class="panel-head">
        <div><p class="eyebrow">Pengesahan Identiti</p><h3>Semakan Kad OKU</h3><p>Status semasa: <strong>{{ $oku->verification_status }}</strong>@if($oku->verified_at) · {{ $oku->verified_at->format('d M Y, H:i') }}@endif</p></div>
        <div class="page-actions">
            @if($oku->oku_card_image_path)<a class="btn" href="{{ route('oku.document',[$oku,'card']) }}">Lihat Kad OKU</a>@endif
            @if($oku->resume_path)<a class="btn" href="{{ route('oku.document',[$oku,'resume']) }}">Muat Turun Résumé</a>@endif
        </div>
    </div>
    @if($oku->oku_card_image_path)
        <form class="verification-form" method="post" action="{{ route('oku.verify',$oku) }}">
            @csrf
            @method('PUT')
            <div class="form-group"><label for="verification_status">Keputusan semakan</label><select class="select" id="verification_status" name="verification_status" required><option value="Verified">Sahkan Kad OKU</option><option value="Rejected">Tolak / perlu pembetulan</option></select></div>
            <div class="form-group"><label for="verification_notes">Catatan pegawai</label><input class="field" id="verification_notes" name="verification_notes" value="{{ old('verification_notes',$oku->verification_notes) }}" placeholder="Wajib jika semakan ditolak"></div>
            <button class="btn btn-primary" type="submit">Simpan Keputusan</button>
        </form>
    @else
        <div class="empty">Pengguna belum memuat naik gambar Kad OKU.</div>
    @endif
</section>
<div class="dashboard-grid"><section class="panel"><div class="panel-head"><div><h3>Cadangan Pekerjaan</h3><p>Padanan berdasarkan kategori dan status pekerjaan</p></div><a class="btn" href="{{ route('oku.find-jobs',$oku) }}">Lihat Semua</a></div><div class="quick-list">@forelse($matchingJobs as $job)<div class="quick-link"><span class="metric-icon">◆</span><span><strong>{{ $job->title }}</strong><span>{{ $job->employer->company_name }} · {{ $job->location }}</span></span><span class="match" style="margin-left:auto">{{ $job->match_score }}%</span></div>@empty<div class="empty">Tiada pekerjaan sepadan.</div>@endforelse</div></section><aside class="panel"><div class="panel-head"><div><h3>Ringkasan Profil</h3></div></div><div class="quick-list"><div class="quick-link"><span><strong>Umur</strong><span>{{ $oku->age }} tahun</span></span></div><div class="quick-link"><span><strong>Pendidikan</strong><span>{{ $oku->education_level }}</span></span></div><div class="quick-link"><span><strong>Nama Pekerjaan</strong><span>{{ $oku->job_name ?: 'Tidak dinyatakan' }}</span></span></div><div class="quick-link"><span><strong>Jenis Bantuan</strong><span>{{ $oku->assistance_type ?: 'Tiada / tidak dinyatakan' }}</span></span></div><div class="quick-link"><span><strong>Telefon</strong><span>{{ $oku->phone_number ?: 'Tidak dinyatakan' }}</span></span></div><div class="quick-link"><span><strong>Alamat</strong><span>{{ $oku->address }}</span></span></div></div></aside></div>
<div class="form-actions"><x-delete-record-dialog :action="route('oku.destroy',$oku)" record-type="pengguna OKU" :record-name="$oku->name" :masked-identifier="'******-**-'.substr(preg_replace('/\D/','',$oku->ic_number),-4)" effect="Tindakan ini mungkin menjejaskan rekod pekerjaan, pengesahan dan laporan." permission="oku_user.delete"/></div>
@endsection
