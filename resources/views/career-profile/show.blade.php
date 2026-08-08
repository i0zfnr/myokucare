@extends('layout', ['title' => 'Profil Kerjaya Saya'])

@section('content')
@php
    $status = $oku?->verification_status ?? 'Pending';
    $statusLabel = ['Pending' => 'Menunggu Semakan', 'Verified' => 'Disahkan JKM', 'Rejected' => 'Perlu Pembetulan'][$status];
@endphp
<div class="page-head career-page-head">
    <div>
        <p class="eyebrow">{{ __('ui.profil_pencari_kerja.c03a6c08') }}</p>
        <h2>{{ $oku ? 'Kemaskini profil kerjaya' : 'Lengkapkan profil kerjaya' }}</h2>
        <p>{{ __('ui.maklumat_lengkap_membantu_sistem_memberikan_cadangan_pekerjaan.42a947e7') }}</p>
    </div>
    @if($oku)
        <span class="verification-pill status-{{ strtolower($status) }}"><i aria-hidden="true"></i>{{ $statusLabel }}</span>
    @endif
</div>

@if($errors->any())
    <div class="error"><strong>{{ __('ui.maklumat_belum_dapat_disimpan.095d035f') }}</strong><br>{{ $errors->first() }}</div>
@endif

<form method="post" action="{{ route('career-profile.save') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <section class="career-layout">
        <div class="career-main">
            <article class="panel career-section">
                <div class="section-heading"><span>01</span><div><h3>{{ __('ui.maklumat_peribadi.d33af1a7') }}</h3><p>{{ __('ui.identiti_dan_maklumat_hubungan_anda.7eff5105') }}</p></div></div>
                <div class="form-grid">
                    <div class="form-group full"><label for="name">{{ __('ui.nama_penuh.46f89b95') }}</label><input class="field" id="name" name="name" value="{{ old('name', $oku?->name ?? auth()->user()->name) }}" required></div>
                    <div class="form-group"><label for="ic_number">{{ __('ui.nombor_kad_pengenalan.0c585a2f') }}</label><input class="field" id="ic_number" name="ic_number" value="{{ old('ic_number', $oku?->ic_number) }}" required></div>
                    <div class="form-group"><label for="phone_number">{{ __('ui.nombor_telefon.b71c491c') }}</label><input class="field" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $oku?->phone_number) }}" required></div>
                    <div class="form-group"><label for="gender">{{ __('ui.jantina.652f8b82') }}</label><select class="select" id="gender" name="gender" required><option value="">{{ __('ui.pilih_jantina.70ddd2fe') }}</option>@foreach(['Lelaki','Perempuan'] as $value)<option @selected(old('gender',$oku?->gender)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="age">{{ __('ui.umur.133cb71a') }}</label><input class="field" id="age" name="age" type="number" min="1" max="120" value="{{ old('age', $oku?->age) }}" required></div>
                    <div class="form-group"><label for="marital_status">{{ __('ui.status_perkahwinan.9b26b931') }}</label><select class="select" id="marital_status" name="marital_status" required><option value="">{{ __('ui.pilih_status.6b38bfb5') }}</option>@foreach(['Berkahwin','Bujang','Duda','Janda'] as $value)<option @selected(old('marital_status',$oku?->marital_status)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group full"><label for="address">{{ __('ui.alamat.85b6ed5c') }}</label><textarea class="textarea" id="address" name="address" rows="3" required>{{ old('address', $oku?->address) }}</textarea></div>
                    <x-besut-residence-fields :oku="$oku" />
                </div>
            </article>

            <article class="panel career-section">
                <div class="section-heading"><span>02</span><div><h3>{{ __('ui.pengesahan_kad_oku.35b85542') }}</h3><p>{{ __('ui.muat_naik_gambar_yang_jelas_untuk_semakan.ba796bbb') }}</p></div></div>
                <div class="form-grid">
                    <div class="form-group"><label for="oku_card_number">{{ __('ui.nombor_pendaftaran_oku.35b40f68') }}</label><input class="field" id="oku_card_number" name="oku_card_number" value="{{ old('oku_card_number', $oku?->oku_card_number) }}" required></div>
                    <div class="form-group"><label for="oku_category">{{ __('ui.kategori_oku.5a4ba70d') }}</label><select class="select" id="oku_category" name="oku_category" required><option value="">{{ __('ui.pilih_kategori.5322c62f') }}</option>@foreach(['Fizikal','Penglihatan','Pendengaran','Pertuturan','Pembelajaran','Mental','Pelbagai'] as $value)<option @selected(old('oku_category',$oku?->oku_category)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="sektor_pekerjaan">{{ __('ui.sektor_pekerjaan.0e1545e5') }}</label><select class="select" id="sektor_pekerjaan" name="sektor_pekerjaan"><option value="">{{ __('ui.pilih_sektor.3c0e74ef') }}</option>@foreach(['Sektor Awam','Sektor Swasta','Bekerja Sendiri','Tidak Bekerja'] as $value)<option @selected(old('sektor_pekerjaan',$oku?->sektor_pekerjaan)===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group full">
                        <label for="oku_card_image">{{ __('ui.gambar_kad_oku.7508a6d6') }} <small>{{ __('ui.jpg_png_atau_webp_maksimum_5mb.af7a8a87') }}</small></label>
                        <input class="field file-field" id="oku_card_image" name="oku_card_image" type="file" accept="image/jpeg,image/png,image/webp">
                        @if($oku?->oku_card_image_path)<a class="document-link" href="{{ route('career-profile.document','card') }}">{{ __('ui.lihat_dokumen_kad_oku_semasa.fd503309') }}</a>@endif
                    </div>
                    @if($oku?->verification_notes)
                        <div class="verification-note full"><strong>{{ __('ui.catatan_pegawai_jkm.c3ac2550') }}</strong><p>{{ $oku->verification_notes }}</p></div>
                    @endif
                </div>
            </article>

            <article class="panel career-section">
                <div class="section-heading"><span>03</span><div><h3>{{ __('ui.profil_kerjaya.c6f72da7') }}</h3><p>{{ __('ui.pendidikan_kemahiran_dan_resume_untuk_padanan_kerja.c7d96b8d') }}</p></div></div>
                <div class="form-grid">
                    <x-education-level-field
                        :value="old('education_level', $oku?->education_level)"
                        :error="$errors->first('education_level')"
                    />
                    <div class="form-group"><label for="availability_status">{{ __('ui.status_ketersediaan.0863bd34') }}</label><select class="select" id="availability_status" name="availability_status" required>@foreach(['Mencari Kerja','Sudah Bekerja','Tidak Tersedia'] as $value)<option @selected(old('availability_status',$oku?->availability_status ?? 'Mencari Kerja')===$value)>{{ $value }}</option>@endforeach</select></div>
                    <div class="form-group full"><label for="career_summary">{{ __('ui.ringkasan_kerjaya.544ad33d') }}</label><textarea class="textarea" id="career_summary" name="career_summary" rows="4" placeholder="{{ __('ui.ceritakan_pengalaman_dan_jenis_pekerjaan_yang_anda.c2226229') }}">{{ old('career_summary', $oku?->career_summary) }}</textarea></div>
                    <div class="form-group full"><label for="skills">{{ __('ui.kemahiran.22ff70cb') }}</label><textarea class="textarea" id="skills" name="skills" rows="3" placeholder="{{ __('ui.contoh_microsoft_office_khidmat_pelanggan_reka_bentuk.31de599e') }}">{{ old('skills', $oku?->skills) }}</textarea></div>
                    @php $bantuanList=old('jenis_bantuan',$oku?->jenis_bantuan??[]); @endphp
                    <div class="form-group full"><label>{{ __('ui.jenis_bantuan_pilih_yang_berkenaan.ff0fb742') }}</label><div style="display:flex;flex-wrap:wrap;gap:8px">@foreach(['EPOKU'=>'Elaun Pekerja OKU','BTB'=>'Bantuan OKU Tidak Berupaya Bekerja','BPT'=>'Bantuan Penjagaan OKU Terlantar','BAT'=>'Bantuan Alat Sokongan/Tiruan','Lain-lain'=>'Lain-lain','Tiada'=>'Tiada'] as $val=>$label)<label class="check-option" style="margin:0!important"><input name="jenis_bantuan[]" type="checkbox" value="{{ $val }}" @if(is_array($bantuanList)&&in_array($val,$bantuanList,true)) checked @endif><span>{{ $label }}</span></label>@endforeach</div></div>
                    <div class="form-group full">
                        <label for="resume">{{ __('ui.resume.9fb58963') }} <small>{{ __('ui.pdf_doc_atau_docx_maksimum_5mb.702ae3ae') }}</small></label>
                        <input class="field file-field" id="resume" name="resume" type="file" accept=".pdf,.doc,.docx">
                        @if($oku?->resume_path)<a class="document-link" href="{{ route('career-profile.document','resume') }}">{{ __('ui.muat_turun_resume_semasa.1b6087b9') }}</a>@endif
                    </div>
                </div>
            </article>
        </div>

        <aside class="career-aside">
            <article class="panel profile-progress">
                <span class="profile-progress-icon" aria-hidden="true">◎</span>
                <h3>{{ __('ui.status_profil.d5cd3724') }}</h3>
                <p>{{ $oku ? 'Profil anda telah diwujudkan. Pastikan dokumen sentiasa terkini.' : 'Lengkapkan borang untuk mengaktifkan profil pencari kerja.' }}</p>
                <ul>
                    <li class="{{ $oku ? 'done' : '' }}">{{ __('ui.maklumat_peribadi.e30f873c') }}</li>
                    <li class="{{ $oku?->oku_card_image_path ? 'done' : '' }}">Gambar Kad OKU</li>
                    <li class="{{ $oku?->resume_path ? 'done' : '' }}">Résumé kerjaya</li>
                    <li class="{{ $status === 'Verified' ? 'done' : '' }}">{{ __('ui.pengesahan_jkm.bee6a989') }}</li>
                </ul>
            </article>
            <button class="btn btn-primary career-save" type="submit">{{ __('ui.simpan_profil_kerjaya.508a5c35') }}</button>
            <p class="privacy-copy">{{ __('ui.dokumen_anda_disimpan_secara_peribadi_dan_hanya.c82c420a') }}</p>
        </aside>
    </section>
</form>
@endsection
