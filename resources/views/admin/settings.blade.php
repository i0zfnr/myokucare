@extends('layout',['title'=>'Tetapan Pentadbir'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Konfigurasi Kakitangan</p><h2>Tetapan Sistem</h2><p>Sesuaikan paparan, kemas kini data dan pemberitahuan untuk akaun anda.</p></div></div>
<form class="admin-settings-form" method="post" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
<section class="panel settings-section">
    <div class="settings-section-icon" aria-hidden="true">Aa</div><div class="settings-section-copy"><h3>Paparan & Aksesibiliti</h3><p>Tetapan ini akan digunakan pada peranti selepas halaman dimuat semula.</p></div>
    <div class="settings-fields">
        <div class="form-group"><label for="font-scale">Saiz teks lalai</label><select class="select" id="font-scale" name="font_scale">@foreach(['100'=>'Biasa (100%)','112.5'=>'Besar (112.5%)','125'=>'Lebih besar (125%)','137.5'=>'Maksimum (137.5%)'] as $value=>$label)<option value="{{ $value }}" @selected((string)$preferences['font_scale']===$value)>{{ $label }}</option>@endforeach</select></div>
        <label class="setting-toggle"><input type="hidden" name="high_contrast_default" value="0"><input type="checkbox" name="high_contrast_default" value="1" @checked($preferences['high_contrast_default'])><span><strong>Kontras tinggi secara lalai</strong><small>Gunakan warna dan sempadan yang lebih jelas.</small></span></label>
        <label class="setting-toggle"><input type="hidden" name="compact_sidebar" value="0"><input type="checkbox" name="compact_sidebar" value="1" @checked($preferences['compact_sidebar'])><span><strong>Sidebar kompak</strong><small>Kurangkan sedikit lebar navigasi pada desktop.</small></span></label>
        <label class="setting-toggle"><input type="hidden" name="show_help_panel" value="0"><input type="checkbox" name="show_help_panel" value="1" @checked($preferences['show_help_panel'])><span><strong>Tunjukkan panel bantuan</strong><small>Paparkan panduan ringkas di bahagian bawah sidebar.</small></span></label>
    </div>
</section>
<section class="panel settings-section">
    <div class="settings-section-icon refresh" aria-hidden="true">↻</div><div class="settings-section-copy"><h3>Data & Paparan Senarai</h3><p>Kawal kekerapan statistik dan jumlah rekod lalai.</p></div>
    <div class="settings-fields two-column">
        <div class="form-group"><label for="refresh-seconds">Kemas kini dashboard</label><select class="select" id="refresh-seconds" name="dashboard_refresh_seconds">@foreach([10=>'Setiap 10 saat',30=>'Setiap 30 saat',60=>'Setiap 1 minit',120=>'Setiap 2 minit'] as $value=>$label)<option value="{{ $value }}" @selected((int)$preferences['dashboard_refresh_seconds']===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="form-group"><label for="page-size">Rekod setiap halaman</label><select class="select" id="page-size" name="default_page_size">@foreach([10,15,25,50] as $value)<option value="{{ $value }}" @selected((int)$preferences['default_page_size']===$value)>{{ $value }} rekod</option>@endforeach</select></div>
    </div>
</section>
<section class="panel settings-section">
    <div class="settings-section-icon notification" aria-hidden="true">!</div><div class="settings-section-copy"><h3>Pemberitahuan</h3><p>Pilih pemberitahuan operasi yang anda mahu terima.</p></div>
    <div class="settings-fields"><label class="setting-toggle"><input type="hidden" name="email_case_notifications" value="0"><input type="checkbox" name="email_case_notifications" value="1" @checked($preferences['email_case_notifications'])><span><strong>Pemberitahuan kes melalui e-mel</strong><small>Simpan pilihan untuk pemberitahuan permohonan yang memerlukan perhatian.</small></span></label></div>
</section>
<div class="settings-save-bar"><span>Tetapan disimpan khusus untuk akaun kakitangan ini.</span><button class="btn btn-primary" type="submit">Simpan Tetapan</button></div>
</form>
@endsection
