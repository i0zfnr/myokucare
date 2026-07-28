@extends('layout',['title'=>'Profil OKU'])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.profil_individu.905aa690') }}</p><h2>{{ __('ui.maklumat_oku.dc685614') }}</h2></div><div style="display:flex;gap:10px"><a class="btn" href="{{ route('oku.index') }}">{{ __('ui.senarai.c0d6d26e') }}</a><a class="btn btn-primary" href="{{ route('oku.edit',$oku) }}">{{ __('ui.kemaskini.356ee342') }}</a></div></div>
<section class="panel profile-hero"><span class="profile-avatar">{{ collect(explode(' ',$oku->name))->take(2)->map(fn($v)=>strtoupper(substr($v,0,1)))->implode('') }}</span><div><h2>{{ $oku->name }}</h2><p>{{ $oku->ic_number }} · {{ $oku->oku_card_number }}</p><div style="margin-top:10px"><span class="badge">{{ $oku->oku_category }}</span> <span class="badge">{{ $oku->employment_status }}</span></div></div></section>
<section class="panel staff-verification">
    <div class="panel-head">
        <div><p class="eyebrow">{{ __('ui.pengesahan_identiti.a58b0502') }}</p><h3>{{ __('ui.semakan_kad_oku.8a00058d') }}</h3><p>{{ __('ui.status_semasa.5a9ee215') }} <strong>{{ $oku->verification_status }}</strong>@if($oku->verified_at) · {{ $oku->verified_at->format('d M Y, H:i') }}@endif</p></div>
        <div class="page-actions">
            @if($oku->oku_card_image_path)<a class="btn" href="{{ route('oku.document',[$oku,'card']) }}">{{ __('ui.lihat_kad_oku.e9fa5c38') }}</a>@endif
            @if($oku->resume_path)<a class="btn" href="{{ route('oku.document',[$oku,'resume']) }}">{{ __('ui.muat_turun_resume.aa471404') }}</a>@endif
        </div>
    </div>
    @if($oku->oku_card_image_path)
        <form class="verification-form" method="post" action="{{ route('oku.verify',$oku) }}">
            @csrf
            @method('PUT')
            <div class="form-group"><label for="verification_status">{{ __('ui.keputusan_semakan.0d1cf1f9') }}</label><select class="select" id="verification_status" name="verification_status" required><option value="Verified">{{ __('ui.sahkan_kad_oku.73aa1af6') }}</option><option value="Rejected">{{ __('ui.tolak_perlu_pembetulan.457974ae') }}</option></select></div>
            <div class="form-group"><label for="verification_notes">{{ __('ui.catatan_pegawai.57006bce') }}</label><input class="field" id="verification_notes" name="verification_notes" value="{{ old('verification_notes',$oku->verification_notes) }}" placeholder="{{ __('ui.wajib_jika_semakan_ditolak.40082fe3') }}"></div>
            <button class="btn btn-primary" type="submit">{{ __('ui.simpan_keputusan.94bc7836') }}</button>
        </form>
    @else
        <div class="empty">{{ __('ui.pengguna_belum_memuat_naik_gambar_kad_oku.d9587d38') }}</div>
    @endif
</section>
<div class="dashboard-grid"><section class="panel"><div class="panel-head"><div><h3>{{ __('ui.cadangan_pekerjaan.fd10f034') }}</h3><p>{{ __('ui.padanan_berdasarkan_kategori_dan_status_pekerjaan.bad36cbd') }}</p></div><a class="btn" href="{{ route('oku.find-jobs',$oku) }}">{{ __('ui.lihat_semua.2adf9761') }}</a></div><div class="quick-list">@forelse($matchingJobs as $job)<div class="quick-link"><span class="metric-icon">◆</span><span><strong>{{ $job->title }}</strong><span>{{ $job->employer->company_name }} · {{ $job->location }}</span></span><span class="match" style="margin-left:auto">{{ $job->match_score }}%</span></div>@empty<div class="empty">{{ __('ui.tiada_pekerjaan_sepadan.e7c7eb62') }}</div>@endforelse</div></section><aside class="panel"><div class="panel-head"><div><h3>{{ __('ui.ringkasan_profil.1f8d224e') }}</h3></div></div><div class="quick-list"><div class="quick-link"><span><strong>{{ __('ui.umur.133cb71a') }}</strong><span>{{ $oku->age }} tahun</span></span></div><div class="quick-link"><span><strong>{{ __('ui.pendidikan.a1cefd01') }}</strong><span>{{ $oku->education_level }}</span></span></div><div class="quick-link"><span><strong>{{ __('ui.nama_pekerjaan.27ba3ba9') }}</strong><span>{{ $oku->job_name ?: 'Tidak dinyatakan' }}</span></span></div><div class="quick-link"><span><strong>{{ __('ui.jenis_bantuan.a455fbd4') }}</strong><span>{{ $oku->assistance_type ?: 'Tiada / tidak dinyatakan' }}</span></span></div><div class="quick-link"><span><strong>{{ __('ui.telefon.40314f88') }}</strong><span>{{ $oku->phone_number ?: 'Tidak dinyatakan' }}</span></span></div><div class="quick-link"><span><strong>{{ __('ui.alamat.85b6ed5c') }}</strong><span>{{ $oku->address }}</span></span></div></div></aside></div>
<div class="form-actions"><x-delete-record-dialog :action="route('oku.destroy',$oku)" record-type="pengguna OKU" :record-name="$oku->name" :masked-identifier="'******-**-'.substr(preg_replace('/\D/','',$oku->ic_number),-4)" effect="Tindakan ini mungkin menjejaskan rekod pekerjaan, pengesahan dan laporan." permission="oku_user.delete"/></div>
@endsection
