@extends('layout',['title'=>'Tetapan Admin System'])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.konfigurasi_kakitangan.f754100c') }}</p><h2>{{ __('ui.tetapan_sistem.11898d90') }}</h2><p>{{ __('ui.sesuaikan_paparan_kemas_kini_data_dan_pemberitahuan.55e9c462') }}</p></div></div>
<form class="admin-settings-form" method="post" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
<section class="panel settings-section">
    <div class="settings-section-icon" aria-hidden="true">{{ __('ui.aa.2c419ecc') }}</div><div class="settings-section-copy"><h3>{{ __('ui.paparan_aksesibiliti.d4274907') }}</h3><p>{{ __('ui.tetapan_ini_akan_digunakan_pada_peranti_selepas.e6e76be3') }}</p></div>
    <div class="settings-fields">
        <div class="form-group"><label for="font-scale">{{ __('ui.saiz_teks_lalai.94c1e9be') }}</label><select class="select" id="font-scale" name="font_scale">@foreach(['100'=>'Biasa (100%)','112.5'=>'Besar (112.5%)','125'=>'Lebih besar (125%)','137.5'=>'Maksimum (137.5%)'] as $value=>$label)<option value="{{ $value }}" @selected((string)$preferences['font_scale']===$value)>{{ $label }}</option>@endforeach</select></div>
        <label class="setting-toggle"><input type="hidden" name="high_contrast_default" value="0"><input type="checkbox" name="high_contrast_default" value="1" @checked($preferences['high_contrast_default'])><span><strong>{{ __('ui.kontras_tinggi_secara_lalai.cbf62bf8') }}</strong><small>{{ __('ui.gunakan_warna_dan_sempadan_yang_lebih_jelas.54e7c8cd') }}</small></span></label>
        <label class="setting-toggle"><input type="hidden" name="compact_sidebar" value="0"><input type="checkbox" name="compact_sidebar" value="1" @checked($preferences['compact_sidebar'])><span><strong>{{ __('ui.sidebar_kompak.6c412b8e') }}</strong><small>{{ __('ui.kurangkan_sedikit_lebar_navigasi_pada_desktop.8c7ae7d9') }}</small></span></label>
        <label class="setting-toggle"><input type="hidden" name="show_help_panel" value="0"><input type="checkbox" name="show_help_panel" value="1" @checked($preferences['show_help_panel'])><span><strong>{{ __('ui.tunjukkan_panel_bantuan.ac933acd') }}</strong><small>{{ __('ui.paparkan_panduan_ringkas_di_bahagian_bawah_sidebar.3c9537fc') }}</small></span></label>
    </div>
</section>
<section class="panel settings-section">
    <div class="settings-section-icon refresh" aria-hidden="true">↻</div><div class="settings-section-copy"><h3>{{ __('ui.data_paparan_senarai.ce52c5bf') }}</h3><p>{{ __('ui.kawal_kekerapan_statistik_dan_jumlah_rekod_lalai.7aefcf67') }}</p></div>
    <div class="settings-fields two-column">
        <div class="form-group"><label for="refresh-seconds">{{ __('ui.kemas_kini_dashboard.370211cd') }}</label><select class="select" id="refresh-seconds" name="dashboard_refresh_seconds">@foreach([10=>'Setiap 10 saat',30=>'Setiap 30 saat',60=>'Setiap 1 minit',120=>'Setiap 2 minit'] as $value=>$label)<option value="{{ $value }}" @selected((int)$preferences['dashboard_refresh_seconds']===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="form-group"><label for="page-size">{{ __('ui.rekod_setiap_halaman.8cd8d328') }}</label><select class="select" id="page-size" name="default_page_size">@foreach([10,15,25,50] as $value)<option value="{{ $value }}" @selected((int)$preferences['default_page_size']===$value)>{{ $value }} rekod</option>@endforeach</select></div>
    </div>
</section>
<section class="panel settings-section">
    <div class="settings-section-icon notification" aria-hidden="true">!</div><div class="settings-section-copy"><h3>{{ __('ui.pemberitahuan.00357dbf') }}</h3><p>{{ __('ui.pilih_pemberitahuan_operasi_yang_anda_mahu_terima.5767640c') }}</p></div>
    <div class="settings-fields"><label class="setting-toggle"><input type="hidden" name="email_case_notifications" value="0"><input type="checkbox" name="email_case_notifications" value="1" @checked($preferences['email_case_notifications'])><span><strong>{{ __('ui.pemberitahuan_kes_melalui_e_mel.ccadd50c') }}</strong><small>{{ __('ui.simpan_pilihan_untuk_pemberitahuan_permohonan_yang_memerlukan.081be9b3') }}</small></span></label></div>
</section>
<div class="settings-save-bar"><span>{{ __('ui.tetapan_disimpan_khusus_untuk_akaun_kakitangan_ini.ca19b3b1') }}</span><button class="btn btn-primary" type="submit">{{ __('ui.simpan_tetapan.f8b8eebd') }}</button></div>
</form>
@endsection
