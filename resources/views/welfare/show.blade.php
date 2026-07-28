@extends('layout',['title'=>'Butiran Permohonan Kebajikan'])
@section('content')
@php $labels=['Pending'=>'Menunggu','Under Review'=>'Dalam Semakan','Approved'=>'Diluluskan','Rejected'=>'Ditolak']; @endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.kes_kebajikan.857d5d70') }}</p><h2>{{ __('ui.butiran_permohonan.533a90e7') }}</h2><p>Rujukan #{{ str_pad($application->id,6,'0',STR_PAD_LEFT) }} · diterima {{ $application->application_date->format('d/m/Y') }}</p></div>
    <a class="btn" href="{{ route('welfare.index') }}">{{ __('ui.kembali.0b8ff91a') }}</a>
</div>

<section class="welfare-detail-grid">
    <article class="panel welfare-case-summary">
        <div class="panel-head"><div><h3>{{ $application->application_type }}</h3><p>{{ __('ui.maklumat_permohonan_dan_pemohon.02c37234') }}</p></div><span class="welfare-status status-{{ str($application->status)->slug() }}"><span></span>{{ $labels[$application->status] }}</span></div>
        <dl class="detail-list">
            <div><dt>{{ __('ui.nama_pemohon.616ccbba') }}</dt><dd>{{ $application->oku->name }}</dd></div>
            <div><dt>{{ __('ui.nombor_kad_oku.909c0d55') }}</dt><dd>{{ $application->oku->oku_card_number }}</dd></div>
            <div><dt>{{ __('ui.kategori_oku.5a4ba70d') }}</dt><dd>{{ $application->oku->oku_category }}</dd></div>
            <div><dt>{{ __('ui.tarikh_permohonan.63c68240') }}</dt><dd>{{ $application->application_date->format('d/m/Y') }}</dd></div>
            <div class="full"><dt>{{ __('ui.penerangan_keperluan.4b54a6b5') }}</dt><dd>{{ $application->notes ?: 'Tiada penerangan tambahan.' }}</dd></div>
            @if($isStaff && ($translation=$application->translations->firstWhere('field_name','notes')))
            <div class="full"><dt>{{ __('translation.translated_view') }}</dt><dd>{{ app()->getLocale()==='en' ? ($translation->translated_text_en ?: __('translation.pending')) : ($translation->translated_text_bm ?: __('translation.pending')) }}</dd></div>
            <div class="full"><dt>{{ __('translation.original_text') }} ({{ $translation->original_language }})</dt><dd>{{ $translation->original_text }}</dd></div>
            <div><dt>{{ __('translation.confidence') }}</dt><dd>{{ number_format((float)$translation->translation_confidence*100) }}%</dd></div>
            @endif
            @if($application->rejection_reason)<div class="full rejection-note"><dt>{{ __('ui.sebab_penolakan.c923df26') }}</dt><dd>{{ $application->rejection_reason }}</dd></div>@endif
        </dl>
    </article>
    <aside class="panel welfare-review-summary">
        <div class="panel-head"><div><h3>{{ __('ui.status_semakan.47cbbb22') }}</h3><p>{{ __('ui.rekod_tindakan_pegawai.1ce9597f') }}</p></div></div>
        <dl class="detail-list single">
            <div><dt>{{ __('ui.pegawai_penyemak.f0ae4f71') }}</dt><dd>{{ $application->reviewer?->name ?? 'Belum ditugaskan' }}</dd></div>
            <div><dt>{{ __('ui.tarikh_semakan.6aea8b52') }}</dt><dd>{{ $application->review_date?->format('d/m/Y') ?? 'Belum disemak' }}</dd></div>
            <div><dt>{{ __('ui.semakan_seterusnya.410aee65') }}</dt><dd>{{ $application->next_review_date?->format('d/m/Y') ?? 'Belum ditetapkan' }}</dd></div>
        </dl>
    </aside>
</section>

@if($isStaff)
<section class="welfare-management-grid">
    <form class="panel case-management-form" method="post" action="{{ route('welfare.update-status',$application) }}">@csrf @method('PUT')
        <div class="panel-head"><div><h3>{{ __('ui.kemaskini_keputusan.761f62b8') }}</h3><p>{{ __('ui.catat_status_semakan_kes_semasa.8d5c3708') }}</p></div></div>
        <div class="form-group"><label for="case-status">{{ __('ui.status.bae7d5be') }}</label><select class="select" id="case-status" name="status">@foreach($labels as $value=>$label)<option value="{{ $value }}" @selected($application->status===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="form-group"><label for="rejection-reason">{{ __('ui.sebab_penolakan.c923df26') }}</label><textarea class="textarea" id="rejection-reason" name="rejection_reason" rows="3" placeholder="{{ __('ui.wajib_jika_permohonan_ditolak.05d43d9c') }}">{{ old('rejection_reason',$application->rejection_reason) }}</textarea></div>
        <button class="btn btn-primary" type="submit">{{ __('ui.simpan_status.e162190b') }}</button>
    </form>
    <form class="panel case-management-form" method="post" action="{{ route('welfare.schedule-review',$application) }}">@csrf
        <div class="panel-head"><div><h3>{{ __('ui.jadual_semakan.0acc4e10') }}</h3><p>{{ __('ui.tetapkan_tindakan_susulan_kes.fbaaf677') }}</p></div></div>
        <div class="form-group"><label for="scheduled-date">{{ __('ui.tarikh_semakan.6aea8b52') }}</label><input class="field" id="scheduled-date" name="scheduled_date" type="date" min="{{ today()->format('Y-m-d') }}" required></div>
        <div class="form-group"><label for="schedule-notes">{{ __('ui.catatan_pegawai.57006bce') }}</label><textarea class="textarea" id="schedule-notes" name="notes" rows="3" maxlength="1000"></textarea></div>
        <button class="btn" type="submit">{{ __('ui.tetapkan_jadual.40ba307e') }}</button>
    </form>
</section>
@endif
@endsection
