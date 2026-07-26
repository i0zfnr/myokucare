@extends('layout', ['title' => 'Pengesahan Profil'])

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">Pengesahan Tiga Bulanan</p>
        <h2>Sahkan maklumat terkini anda</h2>
        <p>Maklumat ini perlu disemak setiap tiga bulan supaya peluang pekerjaan dan bantuan yang dipaparkan kekal sesuai.</p>
    </div>
</div>

@if(session('warning'))
    <div class="error" role="alert">{{ session('warning') }}</div>
@endif

@if($errors->any())
    <div class="error form-error-summary" role="alert">
        <strong>Sila semak maklumat berikut:</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@if(!$oku)
    <section class="panel form-section">
        <div class="form-section-head"><span aria-hidden="true">!</span><div><h3>Profil OKU tidak ditemui</h3><p>Sila log keluar dan hubungi pentadbir untuk mendapatkan bantuan.</p></div></div>
    </section>
@else
    <form method="post" action="{{ route('quarterly-profile.update') }}">
        @csrf
        @method('PUT')

        <section class="panel form-section" aria-labelledby="review-title">
            <div class="form-section-head">
                <span aria-hidden="true">✓</span>
                <div>
                    <h3 id="review-title">Maklumat yang perlu disahkan</h3>
                    <p>Kemas kini maklumat yang berubah, atau sahkan jika semuanya masih sama.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="employment_status">Status pekerjaan <span class="required-mark">*</span></label>
                    <select class="select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status" required>
                        @foreach(['Bekerja', 'Tidak Bekerja', 'Sendiri'] as $status)
                            <option value="{{ $status }}" @selected(old('employment_status', $oku->employment_status) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone_number">Nombor telefon <span class="required-mark">*</span></label>
                    <input class="field @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" type="tel" maxlength="20" value="{{ old('phone_number', $oku->phone_number) }}" autocomplete="tel" required>
                </div>

                <div class="form-group full">
                    <label for="address">Alamat semasa <span class="required-mark">*</span></label>
                    <textarea class="textarea @error('address') is-invalid @enderror" id="address" name="address" rows="4" maxlength="2000" autocomplete="street-address" required>{{ old('address', $oku->address) }}</textarea>
                </div>

                <div class="form-group full">
                    <label class="check-option" for="confirm_information">
                        <input id="confirm_information" name="confirm_information" type="checkbox" value="1" required @checked(old('confirm_information'))>
                        <span>Saya mengesahkan bahawa maklumat di atas adalah terkini dan tepat.</span>
                    </label>
                    <small class="field-help">Jika tiada perubahan, semak maklumat dan tandakan pengesahan ini untuk meneruskan.</small>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Sahkan dan Teruskan</button>
        </div>
    </form>
@endif
@endsection
