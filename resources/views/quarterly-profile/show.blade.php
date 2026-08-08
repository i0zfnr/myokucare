@extends('layout', ['title' => 'Pengesahan Profil'])

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">{{ __('ui.pengesahan_tiga_bulanan.b806d73f') }}</p>
        <h2>{{ __('ui.sahkan_maklumat_terkini_anda.db675fa9') }}</h2>
        <p>{{ __('ui.maklumat_ini_perlu_disemak_setiap_tiga_bulan.67e5e66c') }}</p>
    </div>
</div>

@if(session('warning'))
    <div class="error" role="alert">{{ session('warning') }}</div>
@endif

@if($errors->any())
    <div class="error form-error-summary" role="alert">
        <strong>{{ __('ui.sila_semak_maklumat_berikut.f29d1fe9') }}</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@if(!$oku)
    <section class="panel form-section">
        <div class="form-section-head"><span aria-hidden="true">!</span><div><h3>{{ __('ui.profil_oku_tidak_ditemui.be729019') }}</h3><p>{{ __('ui.sila_log_keluar_dan_hubungi_pentadbir_untuk.bf348e37') }}</p></div></div>
    </section>
@else
    <form method="post" action="{{ route('quarterly-profile.update') }}">
        @csrf
        @method('PUT')

        <section class="panel form-section" aria-labelledby="review-title">
            <div class="form-section-head">
                <span aria-hidden="true">✓</span>
                <div>
                    <h3 id="review-title">{{ __('ui.maklumat_yang_perlu_disahkan.c2a2e996') }}</h3>
                    <p>{{ __('ui.kemas_kini_maklumat_yang_berubah_atau_sahkan.51999e0e') }}</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="employment_status">{{ __('ui.status_pekerjaan.7cb12093') }} <span class="required-mark">*</span></label>
                    <select class="select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status" required>
                        @foreach(['Bekerja', 'Tidak Bekerja', 'Sendiri'] as $status)
                            <option value="{{ $status }}" @selected(old('employment_status', $oku->employment_status) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone_number">{{ __('ui.nombor_telefon.b71c491c') }} <span class="required-mark">*</span></label>
                    <input class="field @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" type="tel" maxlength="20" value="{{ old('phone_number', $oku->phone_number) }}" autocomplete="tel" required>
                </div>

                <div class="form-group full">
                    <label for="address">{{ __('ui.alamat_semasa.f7027e02') }} <span class="required-mark">*</span></label>
                    <textarea class="textarea @error('address') is-invalid @enderror" id="address" name="address" rows="4" maxlength="2000" autocomplete="street-address" required>{{ old('address', $oku->address) }}</textarea>
                </div>

                <x-besut-residence-fields :oku="$oku" />

                <div class="form-group full">
                    <label class="check-option" for="confirm_information">
                        <input id="confirm_information" name="confirm_information" type="checkbox" value="1" required @checked(old('confirm_information'))>
                        <span>{{ __('ui.saya_mengesahkan_bahawa_maklumat_di_atas_adalah.e7c91803') }}</span>
                    </label>
                    <small class="field-help">{{ __('ui.jika_tiada_perubahan_semak_maklumat_dan_tandakan.fd81e271') }}</small>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ __('ui.sahkan_dan_teruskan.15baddf3') }}</button>
        </div>
    </form>
@endif
@endsection
