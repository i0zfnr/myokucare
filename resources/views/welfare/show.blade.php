@extends('layout',['title'=>'Butiran Permohonan Kebajikan'])
@section('content')
@php $labels=['Pending'=>'Menunggu','Under Review'=>'Dalam Semakan','Approved'=>'Diluluskan','Rejected'=>'Ditolak']; @endphp
<div class="page-head">
    <div><p class="eyebrow">Kes Kebajikan</p><h2>Butiran Permohonan</h2><p>Rujukan #{{ str_pad($application->id,6,'0',STR_PAD_LEFT) }} · diterima {{ $application->application_date->format('d/m/Y') }}</p></div>
    <a class="btn" href="{{ route('welfare.index') }}">← Kembali</a>
</div>

<section class="welfare-detail-grid">
    <article class="panel welfare-case-summary">
        <div class="panel-head"><div><h3>{{ $application->application_type }}</h3><p>Maklumat permohonan dan pemohon</p></div><span class="welfare-status status-{{ str($application->status)->slug() }}"><span></span>{{ $labels[$application->status] }}</span></div>
        <dl class="detail-list">
            <div><dt>Nama pemohon</dt><dd>{{ $application->oku->name }}</dd></div>
            <div><dt>Nombor Kad OKU</dt><dd>{{ $application->oku->oku_card_number }}</dd></div>
            <div><dt>Kategori OKU</dt><dd>{{ $application->oku->oku_category }}</dd></div>
            <div><dt>Tarikh permohonan</dt><dd>{{ $application->application_date->format('d/m/Y') }}</dd></div>
            <div class="full"><dt>Penerangan keperluan</dt><dd>{{ $application->notes ?: 'Tiada penerangan tambahan.' }}</dd></div>
            @if($isStaff && ($translation=$application->translations->firstWhere('field_name','notes')))
            <div class="full"><dt>{{ __('translation.translated_view') }}</dt><dd>{{ app()->getLocale()==='en' ? ($translation->translated_text_en ?: __('translation.pending')) : ($translation->translated_text_bm ?: __('translation.pending')) }}</dd></div>
            <div class="full"><dt>{{ __('translation.original_text') }} ({{ $translation->original_language }})</dt><dd>{{ $translation->original_text }}</dd></div>
            <div><dt>{{ __('translation.confidence') }}</dt><dd>{{ number_format((float)$translation->translation_confidence*100) }}%</dd></div>
            @endif
            @if($application->rejection_reason)<div class="full rejection-note"><dt>Sebab penolakan</dt><dd>{{ $application->rejection_reason }}</dd></div>@endif
        </dl>
    </article>
    <aside class="panel welfare-review-summary">
        <div class="panel-head"><div><h3>Status Semakan</h3><p>Rekod tindakan pegawai</p></div></div>
        <dl class="detail-list single">
            <div><dt>Pegawai penyemak</dt><dd>{{ $application->reviewer?->name ?? 'Belum ditugaskan' }}</dd></div>
            <div><dt>Tarikh semakan</dt><dd>{{ $application->review_date?->format('d/m/Y') ?? 'Belum disemak' }}</dd></div>
            <div><dt>Semakan seterusnya</dt><dd>{{ $application->next_review_date?->format('d/m/Y') ?? 'Belum ditetapkan' }}</dd></div>
        </dl>
    </aside>
</section>

@if($isStaff)
<section class="welfare-management-grid">
    <form class="panel case-management-form" method="post" action="{{ route('welfare.update-status',$application) }}">@csrf @method('PUT')
        <div class="panel-head"><div><h3>Kemaskini Keputusan</h3><p>Catat status semakan kes semasa.</p></div></div>
        <div class="form-group"><label for="case-status">Status</label><select class="select" id="case-status" name="status">@foreach($labels as $value=>$label)<option value="{{ $value }}" @selected($application->status===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="form-group"><label for="rejection-reason">Sebab penolakan</label><textarea class="textarea" id="rejection-reason" name="rejection_reason" rows="3" placeholder="Wajib jika permohonan ditolak">{{ old('rejection_reason',$application->rejection_reason) }}</textarea></div>
        <button class="btn btn-primary" type="submit">Simpan Status</button>
    </form>
    <form class="panel case-management-form" method="post" action="{{ route('welfare.schedule-review',$application) }}">@csrf
        <div class="panel-head"><div><h3>Jadual Semakan</h3><p>Tetapkan tindakan susulan kes.</p></div></div>
        <div class="form-group"><label for="scheduled-date">Tarikh semakan</label><input class="field" id="scheduled-date" name="scheduled_date" type="date" min="{{ today()->format('Y-m-d') }}" required></div>
        <div class="form-group"><label for="schedule-notes">Catatan pegawai</label><textarea class="textarea" id="schedule-notes" name="notes" rows="3" maxlength="1000"></textarea></div>
        <button class="btn" type="submit">Tetapkan Jadual</button>
    </form>
</section>
@endif
@endsection
