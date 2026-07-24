@extends('layout', ['title' => 'Profil Kerjaya Saya'])

@section('content')
@php
    $status = $oku?->verification_status ?? 'Pending';
    $statusLabel = ['Pending' => 'Menunggu Semakan', 'Verified' => 'Disahkan JKM', 'Rejected' => 'Perlu Pembetulan'][$status];
@endphp
<div class="page-head career-page-head">
    <div>
        <p class="eyebrow">Profil Pencari Kerja</p>
        <h2>{{ $oku ? 'Kemaskini profil kerjaya' : 'Lengkapkan profil kerjaya' }}</h2>
        <p>Maklumat lengkap membantu sistem memberikan cadangan pekerjaan yang lebih sesuai.</p>
    </div>
    @if($oku)
        <span class="verification-pill status-{{ strtolower($status) }}"><i aria-hidden="true"></i>{{ $statusLabel }}</span>
    @endif
</div>

@if($errors->any())
    <div class="error"><strong>Maklumat belum dapat disimpan.</strong><br>{{ $errors->first() }}</div>
@endif

<form method="post" action="{{ route('career-profile.save') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <section class="career-layout">
        <div class="career-main">
            <article class="panel career-section">
                <div class="section-heading"><span>01</span><div><h3>Maklumat Peribadi</h3><p>Identiti dan maklumat hubungan anda</p></div></div>
                <div class="form-grid">
                    <div class="form-group full"><label for="name">Nama penuh</label><input class="field" id="name" name="name" value="{{ old('name', $oku?->name ?? auth()->user()->name) }}" required></div>
                    <div class="form-group"><label for="ic_number">Nombor kad pengenalan</label><input class="field" id="ic_number" name="ic_number" value="{{ old('ic_number', $oku?->ic_number) }}" required></div>
                    <div class="form-group"><label for="phone_number">Nombor telefon</label><input class="field" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $oku?->phone_number) }}" required></div>
                    <div class="form-group"><label for="gender">Jantina</label><select class="select" id="gender" name="gender" required><option value="">Pilih jantina</option>@foreach(['Lelaki','Perempuan'] as $value)<option @selected(old('gender',$oku?->gender)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="age">Umur</label><input class="field" id="age" name="age" type="number" min="1" max="120" value="{{ old('age', $oku?->age) }}" required></div>
                    <div class="form-group"><label for="marital_status">Status perkahwinan</label><select class="select" id="marital_status" name="marital_status" required><option value="">Pilih status</option>@foreach(['Berkahwin','Bujang','Duda','Janda'] as $value)<option @selected(old('marital_status',$oku?->marital_status)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group full"><label for="address">Alamat</label><textarea class="textarea" id="address" name="address" rows="3" required>{{ old('address', $oku?->address) }}</textarea></div>
                </div>
            </article>

            <article class="panel career-section">
                <div class="section-heading"><span>02</span><div><h3>Pengesahan Kad OKU</h3><p>Muat naik gambar yang jelas untuk semakan pegawai JKM</p></div></div>
                <div class="form-grid">
                    <div class="form-group"><label for="oku_card_number">Nombor pendaftaran OKU</label><input class="field" id="oku_card_number" name="oku_card_number" value="{{ old('oku_card_number', $oku?->oku_card_number) }}" required></div>
                    <div class="form-group"><label for="oku_category">Kategori OKU</label><select class="select" id="oku_category" name="oku_category" required><option value="">Pilih kategori</option>@foreach(['Fizikal','Penglihatan','Pendengaran','Pertuturan','Pembelajaran','Mental','Pelbagai'] as $value)<option @selected(old('oku_category',$oku?->oku_category)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="sektor_pekerjaan">Sektor pekerjaan</label><select class="select" id="sektor_pekerjaan" name="sektor_pekerjaan"><option value="">Pilih sektor</option>@foreach(['Sektor Awam','Sektor Swasta','Bekerja Sendiri','Tidak Bekerja'] as $value)<option @selected(old('sektor_pekerjaan',$oku?->sektor_pekerjaan)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group full">
                        <label for="oku_card_image">Gambar Kad OKU <small>JPG, PNG atau WebP · maksimum 5MB</small></label>
                        <input class="field file-field" id="oku_card_image" name="oku_card_image" type="file" accept="image/jpeg,image/png,image/webp">
                        @if($oku?->oku_card_image_path)<a class="document-link" href="{{ route('career-profile.document','card') }}">Lihat dokumen Kad OKU semasa →</a>@endif
                    </div>
                    @if($oku?->verification_notes)
                        <div class="verification-note full"><strong>Catatan pegawai JKM</strong><p>{{ $oku->verification_notes }}</p></div>
                    @endif
                </div>
            </article>

            <article class="panel career-section">
                <div class="section-heading"><span>03</span><div><h3>Profil Kerjaya</h3><p>Pendidikan, kemahiran dan résumé untuk padanan kerja</p></div></div>
                <div class="form-grid">
                    <div class="form-group"><label for="education_level">Pendidikan tertinggi</label><input class="field" id="education_level" name="education_level" value="{{ old('education_level', $oku?->education_level) }}" required></div>
                    <div class="form-group"><label for="availability_status">Status ketersediaan</label><select class="select" id="availability_status" name="availability_status" required>@foreach(['Mencari Kerja','Sudah Bekerja','Tidak Tersedia'] as $value)<option @selected(old('availability_status',$oku?->availability_status ?? 'Mencari Kerja')===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group full"><label for="career_summary">Ringkasan kerjaya</label><textarea class="textarea" id="career_summary" name="career_summary" rows="4" placeholder="Ceritakan pengalaman dan jenis pekerjaan yang anda cari...">{{ old('career_summary', $oku?->career_summary) }}</textarea></div>
                    <div class="form-group full"><label for="skills">Kemahiran</label><textarea class="textarea" id="skills" name="skills" rows="3" placeholder="Contoh: Microsoft Office, khidmat pelanggan, reka bentuk grafik">{{ old('skills', $oku?->skills) }}</textarea></div>
                    @php $bantuanList=old('jenis_bantuan',$oku?->jenis_bantuan??[]); @endphp
                    <div class="form-group full"><label>Jenis bantuan (pilih yang berkenaan)</label><div style="display:flex;flex-wrap:wrap;gap:8px">@foreach(['EPOKU'=>'Elaun Pekerja OKU','BTB'=>'Bantuan OKU Tidak Berupaya Bekerja','BPT'=>'Bantuan Penjagaan OKU Terlantar','BAT'=>'Bantuan Alat Sokongan/Tiruan','Lain-lain'=>'Lain-lain','Tiada'=>'Tiada'] as $val=>$label)<label class="check-option" style="margin:0!important"><input name="jenis_bantuan[]" type="checkbox" value="{{ $val }}" @if(is_array($bantuanList)&&in_array($val,$bantuanList,true)) checked @endif><span>{{ $label }}</span></label>@endforeach</div></div>
                    <div class="form-group full">
                        <label for="resume">Résumé <small>PDF, DOC atau DOCX · maksimum 5MB</small></label>
                        <input class="field file-field" id="resume" name="resume" type="file" accept=".pdf,.doc,.docx">
                        @if($oku?->resume_path)<a class="document-link" href="{{ route('career-profile.document','resume') }}">Muat turun résumé semasa →</a>@endif
                    </div>
                </div>
            </article>
        </div>

        <aside class="career-aside">
            <article class="panel profile-progress">
                <span class="profile-progress-icon" aria-hidden="true">◎</span>
                <h3>Status Profil</h3>
                <p>{{ $oku ? 'Profil anda telah diwujudkan. Pastikan dokumen sentiasa terkini.' : 'Lengkapkan borang untuk mengaktifkan profil pencari kerja.' }}</p>
                <ul>
                    <li class="{{ $oku ? 'done' : '' }}">Maklumat peribadi</li>
                    <li class="{{ $oku?->oku_card_image_path ? 'done' : '' }}">Gambar Kad OKU</li>
                    <li class="{{ $oku?->resume_path ? 'done' : '' }}">Résumé kerjaya</li>
                    <li class="{{ $status === 'Verified' ? 'done' : '' }}">Pengesahan JKM</li>
                </ul>
            </article>
            <button class="btn btn-primary career-save" type="submit">Simpan Profil Kerjaya</button>
            <p class="privacy-copy">Dokumen anda disimpan secara peribadi dan hanya boleh diakses oleh anda serta pegawai yang diberi kuasa.</p>
        </aside>
    </section>
</form>
@endsection
