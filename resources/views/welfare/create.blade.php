@extends('layout',['title'=>'Permohonan Kebajikan Baharu'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Sokongan Kebajikan</p><h2>Permohonan Baharu</h2><p>Daftarkan keperluan bantuan untuk tindakan dan semakan JKM.</p></div><a class="btn" href="{{ route('welfare.index') }}">← Kembali</a></div>
@if(!$isStaff&&!$oku)
<section class="panel welfare-no-profile"><h3>Profil OKU belum dipautkan</h3><p>Lengkapkan profil OKU terlebih dahulu sebelum membuat permohonan kebajikan.</p>@if(auth()->user()->role==='oku_user')<a class="btn btn-primary" href="{{ route('career-profile.show') }}">Lengkapkan Profil</a>@endif</section>
@else
@if($errors->any())<div class="error" role="alert"><strong>Sila semak maklumat permohonan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="panel welfare-create-form" method="post" action="{{ route('welfare.store') }}">@csrf
    @if($isStaff)
    <div class="form-group full"><label for="oku_id">Pemohon OKU <span class="required-mark">*</span></label><select class="select" id="oku_id" name="oku_id" required><option value="">Pilih pemohon</option>@foreach($okus as $person)<option value="{{ $person->id }}" @selected(old('oku_id')==$person->id)>{{ $person->name }} — {{ $person->oku_card_number }}</option>@endforeach</select></div>
    <div class="form-group"><label for="application_date">Tarikh permohonan <span class="required-mark">*</span></label><input class="field" id="application_date" name="application_date" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ old('application_date',today()->format('Y-m-d')) }}" required></div>
    @else
    <div class="welfare-applicant"><span>Pemohon</span><strong>{{ $oku->name }}</strong><small>{{ $oku->oku_card_number }}</small></div>
    @endif
    <div class="form-group"><label for="application_type">Jenis permohonan <span class="required-mark">*</span></label><input class="field" id="application_type" name="application_type" list="welfare-types" maxlength="100" value="{{ old('application_type') }}" placeholder="Contoh: Bantuan alat sokongan" required><datalist id="welfare-types"><option value="Bantuan Alat Sokongan"><option value="Bantuan Kewangan"><option value="Bantuan Penjagaan"><option value="Bantuan Mobiliti"><option value="Bantuan Latihan"></datalist></div>
    <div class="form-group full"><label for="notes">Penerangan keperluan</label><textarea class="textarea" id="notes" name="notes" rows="5" maxlength="2000" placeholder="Terangkan bantuan yang diperlukan dan maklumat berkaitan.">{{ old('notes') }}</textarea><small class="field-help">Maksimum 2,000 aksara. Elakkan memasukkan maklumat yang tidak berkaitan.</small></div>
    <div class="form-actions full"><a class="btn" href="{{ route('welfare.index') }}">Batal</a><button class="btn btn-primary" type="submit">Hantar Permohonan</button></div>
</form>
@endif
@endsection
