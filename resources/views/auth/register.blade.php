<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Daftar akaun MyOKUcare — platform digital JKM untuk pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan kebajikan.">
    @include('partials.pwa-head')
    <title>Daftar Akaun — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/css/auth.css','resources/css/animate.css','resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#register-form">Langkau ke borang pendaftaran</a>
<main class="register-page">
    <section class="register-side" aria-labelledby="register-brand-heading">
        <div class="register-blob-1"></div>
        <div class="register-blob-2"></div>
        <a class="register-brand" href="{{ route('welcome') }}">
            <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <strong>MyOKUcare</strong>
        </a>
        <div class="register-message">
            <h1 id="register-brand-heading">Sertai komuniti MyOKUcare.</h1>
            <p>Cipta akaun menggunakan e-mel anda. Akses sistem akan disesuaikan berdasarkan peranan yang dipilih.</p>
            <div class="register-benefits" aria-label="Kelebihan sistem">
                <span>Diselia JKM Malaysia</span>
                <span>Percuma sepenuhnya</span>
                <span>Data selamat & terpelihara</span>
            </div>
        </div>
    </section>

    <section class="register-wrap" aria-labelledby="register-heading">
        <a class="back-home" href="{{ route('welcome') }}" aria-label="Kembali ke laman utama">← Kembali ke laman utama</a>
        <form id="register-form" class="register-form" method="post" action="{{ route('register.store') }}" aria-label="Borang pendaftaran" novalidate>
            @csrf

            <p class="eyebrow">Akaun Baharu</p>
            <h2 id="register-heading">Daftar MyOKUcare</h2>
            <p>Lengkapkan maklumat berikut untuk mencipta akaun anda.</p>

            @if($errors->any())
                <div class="register-error" role="alert" aria-live="assertive">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Step Indicator --}}
            <div class="register-steps" role="tablist" aria-label="Langkah pendaftaran">
                <div class="step-indicator active" role="tab" aria-selected="true" aria-controls="step-1-panel" id="step-1-tab">
                    <span class="step-num">1</span>
                    <span class="step-label">Maklumat Akaun</span>
                </div>
                <div class="step-connector" aria-hidden="true"></div>
                <div class="step-indicator" role="tab" aria-selected="false" aria-controls="step-2-panel" id="step-2-tab">
                    <span class="step-num">2</span>
                    <span class="step-label">Maklumat OKU</span>
                </div>
            </div>

            {{-- Step 1: Maklumat Akaun --}}
            <div class="step-panel" id="step-1-panel" role="tabpanel" aria-labelledby="step-1-tab" data-step="1">
                <div class="form-group">
                    <label for="name">Nama penuh <span class="required-note">Wajib</span></label>
                    <input class="field" id="name" name="name" type="text" value="{{ old('name') }}" required aria-required="true" autocomplete="name" placeholder="Nama seperti dalam kad pengenalan">
                </div>
                <div class="form-group">
                    <label for="email">Alamat e-mel <span class="required-note">Wajib</span></label>
                    <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required aria-required="true" autocomplete="email" placeholder="nama@contoh.my">
                </div>
                <div class="form-group">
                    <label for="role">Daftar sebagai <span class="required-note">Wajib</span></label>
                    <select class="select" id="role" name="role" required aria-required="true">
                        <option value="">Pilih peranan</option>
                        <option value="oku_user" @selected(old('role')==='oku_user')>Pengguna OKU</option>
                        <option value="employer" @selected(old('role')==='employer')>Majikan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Kata laluan <span class="required-note">Wajib</span></label>
                    <div class="password-field">
                        <input class="field" id="password" name="password" type="password" required aria-required="true" autocomplete="new-password" placeholder="Minimum 8 aksara">
                        <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Tunjukkan kata laluan" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Sahkan kata laluan <span class="required-note">Wajib</span></label>
                    <div class="password-field">
                        <input class="field" id="password_confirmation" name="password_confirmation" type="password" required aria-required="true" autocomplete="new-password" placeholder="Taip semula kata laluan">
                        <button class="password-toggle" type="button" data-password-toggle aria-controls="password_confirmation" aria-label="Tunjukkan kata laluan" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                        </button>
                    </div>
                </div>
                <div class="step-actions">
                    <button class="btn btn-primary step-next" type="button" data-step-next>Seterusnya →</button>
                </div>
            </div>

            {{-- Step 2: Maklumat OKU --}}
            <div class="step-panel" id="step-2-panel" role="tabpanel" aria-labelledby="step-2-tab" data-step="2" hidden>
                <section class="oku-signup-fields" id="okuSignupFields" @if(old('role')!=='oku_user') hidden @endif aria-label="Maklumat pendaftaran OKU">
                    <div class="signup-section-title">
                        <strong>Maklumat Pendaftaran OKU</strong>
                        <span>Sistem akan menyemak kelengkapan dan rekod pendua sebelum akaun diwujudkan.</span>
                    </div>
                    <p class="signup-card-note">
                        <span aria-hidden="true">ⓘ</span>
                        <span>Gambar Kad OKU tidak diperlukan di halaman ini. Selepas pendaftaran, log masuk dan muat naik gambar kad melalui Profil Kerjaya.</span>
                    </p>
                    <div class="signup-fields-list">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="ic_number">No. kad pengenalan <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="ic_number" name="ic_number" value="{{ old('ic_number') }}" inputmode="numeric" autocomplete="off" required aria-required="true" placeholder="000101-10-1234">
                            </div>
                            <div class="form-group">
                                <label for="phone_number">No. telefon <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" required aria-required="true" placeholder="012-345 6789">
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="gender">Jantina <span class="required-note">Wajib</span></label>
                                <select class="select oku-required" id="gender" name="gender" required aria-required="true">
                                    <option value="">Pilih jantina</option>
                                    @foreach(['Lelaki','Perempuan'] as $value)
                                        <option @selected(old('gender')===$value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="age">Umur <span class="required-note">Wajib</span> <small class="field-hint">min. 16</small></label>
                                <input class="field oku-required" id="age" name="age" type="number" min="16" max="120" value="{{ old('age') }}" required aria-required="true">
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="marital_status">Status perkahwinan <span class="required-note">Wajib</span></label>
                                <select class="select oku-required" id="marital_status" name="marital_status" required aria-required="true">
                                    <option value="">Pilih status</option>
                                    @foreach(['Berkahwin','Bujang','Duda','Janda'] as $value)
                                        <option @selected(old('marital_status')===$value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="education_level">Pendidikan tertinggi <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="education_level" name="education_level" value="{{ old('education_level') }}" required aria-required="true" placeholder="Cth: SPM, Diploma, Ijazah">
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="oku_card_number">No. pendaftaran OKU <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="oku_card_number" name="oku_card_number" value="{{ old('oku_card_number') }}" required aria-required="true">
                            </div>
                            <div class="form-group">
                                <label for="oku_category">Kategori OKU <span class="required-note">Wajib</span></label>
                                <select class="select oku-required" id="oku_category" name="oku_category" required aria-required="true">
                                    <option value="">Pilih kategori</option>
                                    @foreach(['Fizikal','Penglihatan','Pendengaran','Pertuturan','Pembelajaran','Mental','Pelbagai'] as $value)
                                        <option @selected(old('oku_category')===$value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sektor_pekerjaan">Sektor pekerjaan <span class="required-note">Wajib</span></label>
                            <select class="select oku-required" id="sektor_pekerjaan" name="sektor_pekerjaan" required aria-required="true">
                                <option value="">Pilih sektor</option>
                                @foreach(['Sektor Awam','Sektor Swasta','Bekerja Sendiri','Tidak Bekerja'] as $value)
                                    <option @selected(old('sektor_pekerjaan')===$value)>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jenis bantuan (pilih yang berkenaan)</label>
                            <div class="oku-check-grid">
                                @foreach(['EPOKU'=>'Elaun Pekerja OKU','BTB'=>'Bantuan OKU Tidak Berupaya Bekerja','BPT'=>'Bantuan Penjagaan OKU Terlantar','BAT'=>'Bantuan Alat Sokongan/Tiruan','Lain-lain'=>'Lain-lain','Tiada'=>'Tiada'] as $val=>$label)
                                    <label class="check-option">
                                        <input name="jenis_bantuan[]" type="checkbox" value="{{ $val }}" @if(is_array(old('jenis_bantuan'))&&in_array($val,old('jenis_bantuan'),true)) checked @endif>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Alamat penuh <span class="required-note">Wajib</span></label>
                            <textarea class="textarea oku-required" id="address" name="address" rows="3" required aria-required="true">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="step-actions step-actions-2">
                    <button class="btn step-back" type="button" data-step-back>← Kembali</button>
                    <button class="btn btn-primary register-submit btn-ripple" type="submit">Cipta Akaun</button>
                </div>
            </div>

            <p class="signin-note">Sudah mempunyai akaun? <a href="{{ route('login') }}">Log masuk di sini</a></p>
        </form>
    </section>
</main>

{{-- Mobile sticky bottom bar --}}
<div class="register-sticky-bar" id="registerStickyBar" hidden>
    <div class="sticky-bar-inner">
        <span class="sticky-step-text" id="stickyStepText">Langkah 1 daripada 2</span>
        <button class="btn btn-primary sticky-next" type="button" id="stickyNextBtn">Seterusnya →</button>
        <button class="btn btn-primary register-submit sticky-submit" type="submit" form="register-form" id="stickySubmitBtn" hidden>Cipta Akaun</button>
    </div>
</div>

<script>
    (function() {
        var form = document.getElementById('register-form');
        var roleSelect = document.getElementById('role');
        var okuFields = document.getElementById('okuSignupFields');
        var okuRequired = okuFields.querySelectorAll('.oku-required');
        var step1Panel = document.getElementById('step-1-panel');
        var step2Panel = document.getElementById('step-2-panel');
        var step1Tab = document.getElementById('step-1-tab');
        var step2Tab = document.getElementById('step-2-tab');
        var stickyBar = document.getElementById('registerStickyBar');
        var stickyStepText = document.getElementById('stickyStepText');
        var stickyNextBtn = document.getElementById('stickyNextBtn');
        var stickySubmitBtn = document.getElementById('stickySubmitBtn');

        var currentStep = 1;

        function showStep(step) {
            currentStep = step;
            step1Panel.hidden = step !== 1;
            step2Panel.hidden = step !== 2;
            step1Tab.classList.toggle('active', step === 1);
            step2Tab.classList.toggle('active', step === 2);
            step1Tab.setAttribute('aria-selected', step === 1);
            step2Tab.setAttribute('aria-selected', step === 2);

            if (stickyBar) {
                if (step === 1) {
                    stickyStepText.textContent = 'Langkah 1 daripada 2';
                    stickyNextBtn.hidden = false;
                    stickySubmitBtn.hidden = true;
                } else {
                    stickyStepText.textContent = 'Langkah 2 daripada 2';
                    stickyNextBtn.hidden = true;
                    stickySubmitBtn.hidden = false;
                }
            }
        }

        // Step navigation buttons
        document.querySelectorAll('[data-step-next]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!roleSelect || roleSelect.value !== 'oku_user') {
                    // Non-OKU users: submit directly
                    form.submit();
                    return;
                }
                showStep(2);
            });
        });

        document.querySelectorAll('[data-step-back]').forEach(function(btn) {
            btn.addEventListener('click', function() { showStep(1); });
        });

        // Sticky bar next button
        if (stickyNextBtn) {
            stickyNextBtn.addEventListener('click', function() {
                if (!roleSelect || roleSelect.value !== 'oku_user') {
                    form.submit();
                    return;
                }
                showStep(2);
            });
        }

        // Sync OKU fields visibility on role change
        function syncOkuFields() {
            var active = roleSelect && roleSelect.value === 'oku_user';
            if (okuFields) okuFields.hidden = !active;
            okuRequired.forEach(function(field) { field.required = active; });
            // If user switches away from OKU role on step 2, go back to step 1
            if (!active && currentStep === 2) showStep(1);
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', syncOkuFields);
            syncOkuFields();
        }

        // Show sticky bar on mobile (≤850px) when scrolled past form start
        var formTop = form ? form.getBoundingClientRect().top + window.scrollY : 0;
        function checkSticky() {
            if (window.innerWidth > 850) {
                if (stickyBar) stickyBar.hidden = true;
                return;
            }
            var scrollY = window.scrollY;
            var shouldShow = scrollY > formTop + 100;
            if (stickyBar) stickyBar.hidden = !shouldShow;
        }
        window.addEventListener('scroll', checkSticky, { passive: true });
        window.addEventListener('resize', checkSticky);
        checkSticky();

        // Handle visible step 2 after validation errors — show the correct step
        if (document.querySelector('.register-error')) {
            // Check if any step-2 field has a value or error
            var step2Fields = step2Panel.querySelectorAll('.field, .select, .textarea');
            var hasStep2Data = false;
            step2Fields.forEach(function(f) {
                if (f.value && f.value.trim() !== '') hasStep2Data = true;
            });
            if (hasStep2Data) showStep(2);
        }
    })();
</script>
</body></html>
