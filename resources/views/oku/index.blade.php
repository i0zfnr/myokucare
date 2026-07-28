@extends('layout', ['title' => 'Rekod OKU'])

@section('content')
@php
    $hasFilters = collect($filters)->except(['sort_by','sort_direction','per_page'])->filter(fn($value) => filled($value))->isNotEmpty();
    $sortBy = $filters['sort_by'] ?? 'created_at';
    $sortDirection = $filters['sort_direction'] ?? 'desc';
    $sortUrl = function (string $column) use ($sortBy, $sortDirection) {
        $direction = $sortBy === $column && $sortDirection === 'asc' ? 'desc' : 'asc';
        return route('oku.index', array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => $direction, 'page' => null]));
    };
    $sortAria = fn(string $column) => $sortBy === $column ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none';
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">{{ __('ui.pengurusan_oku.da88493c') }}</p>
        <h2>{{ __('ui.senarai_rekod_oku.c3aec10f') }}</h2>
        <p>{{ __('ui.semak_cari_dan_urus_profil_individu_berdaftar.11bdf63b') }}</p>
    </div>
    <div class="page-actions">
        <a class="btn" href="{{ route('reports.export','oku') }}">{{ __('ui.eksport_csv.24844a7f') }}</a>
        <a class="btn btn-primary" href="{{ route('oku.create') }}">{{ __('ui.daftar_oku.0f8aba80') }}</a>
    </div>
</div>

<section class="oku-summary-grid" aria-label="{{ __('ui.ringkasan_rekod_oku.3acd376a') }}">
    @foreach([
        ['tone'=>'coral','icon'=>'id-card','value'=>$stats['total'],'label'=>'Jumlah rekod'],
        ['tone'=>'green','icon'=>'briefcase','value'=>$stats['employed'],'label'=>'Sudah bekerja'],
        ['tone'=>'amber','icon'=>'job-search','value'=>$stats['unemployed'],'label'=>'Belum bekerja'],
        ['tone'=>'purple','icon'=>'welfare','value'=>$stats['pending_verification'],'label'=>'Menunggu pengesahan'],
    ] as $summary)
        <article class="oku-summary-card {{ $summary['tone'] }}">
            <span class="oku-summary-icon" aria-hidden="true"><x-dashboard-icon :name="$summary['icon']" /></span>
            <div><span>{{ $summary['label'] }}</span><strong>{{ number_format($summary['value']) }}</strong><small>{{ __('ui.data_keseluruhan.2864afe1') }}</small></div>
        </article>
    @endforeach
</section>

@if($errors->any())
    <div class="error" role="alert">{{ $errors->first() }}</div>
@endif

<section class="panel oku-filter-panel">
    <div class="oku-filter-head">
        <div><h3>{{ __('ui.cari_dan_tapis_rekod.c37bc28f') }}</h3><p>{{ __('ui.gunakan_satu_atau_beberapa_penapis_untuk_mengecilkan.328a531c') }}</p></div>
        @if($hasFilters)<a href="{{ route('oku.index') }}">{{ __('ui.kosongkan_semua.0e28af4a') }}</a>@endif
    </div>
    <form method="get" action="{{ route('oku.index') }}" role="search" aria-label="{{ __('ui.cari_dan_tapis_rekod_oku.6b49f332') }}">
        <div class="filter-primary">
            <div class="form-group filter-search">
                <label for="search">{{ __('ui.carian_rekod.6d7e4731') }}</label>
                <input class="field" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('ui.nama_nombor_ic_atau_nombor_kad_oku.5b1d4848') }}">
            </div>
            <div class="form-group">
                <label for="category">{{ __('ui.kategori.b7964404') }}</label>
                <select class="select" id="category" name="category">
                    <option value="">{{ __('ui.semua_kategori.3ee43aaf') }}</option>
                    @foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $value)<option @selected(($filters['category'] ?? '')===$value)>{{ $value }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="employment_status">{{ __('ui.status_pekerjaan.7cb12093') }}</label>
                <select class="select" id="employment_status" name="employment_status">
                    <option value="">{{ __('ui.semua_status.baa2adda') }}</option>
                    @foreach(['Bekerja','Tidak Bekerja','Sendiri'] as $value)<option @selected(($filters['employment_status'] ?? '')===$value)>{{ $value }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="verification_status">{{ __('ui.pengesahan_kad_oku.35b85542') }}</label>
                <select class="select" id="verification_status" name="verification_status">
                    <option value="">{{ __('ui.semua_pengesahan.b9b56bb8') }}</option>
                    @foreach(['Pending'=>'Menunggu','Verified'=>'Disahkan','Rejected'=>'Ditolak'] as $value=>$label)
                        <option value="{{ $value }}" @selected(($filters['verification_status'] ?? '')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary filter-submit" type="submit">{{ __('ui.tapis_rekod.e2261d81') }}</button>
        </div>
        <details class="advanced-filters" @if(isset($filters['age_min']) || isset($filters['age_max'])) open @endif>
            <summary>{{ __('ui.penapis_lanjutan.6b372703') }}</summary>
            <div class="advanced-filter-grid">
                <div class="form-group"><label for="age_min">{{ __('ui.umur_minimum.b750d323') }}</label><input class="field" id="age_min" name="age_min" type="number" min="1" max="120" value="{{ $filters['age_min'] ?? '' }}"></div>
                <div class="form-group"><label for="age_max">{{ __('ui.umur_maksimum.37af54d6') }}</label><input class="field" id="age_max" name="age_max" type="number" min="1" max="120" value="{{ $filters['age_max'] ?? '' }}"></div>
                <div class="form-group"><label for="sort_by">{{ __('ui.susun_mengikut.f166ba5e') }}</label><select class="select" id="sort_by" name="sort_by">@foreach(['created_at'=>'Rekod terkini','name'=>'Nama','ic_number'=>'Nombor IC','oku_category'=>'Kategori','employment_status'=>'Status pekerjaan','age'=>'Umur'] as $key=>$label)<option value="{{ $key }}" @selected($sortBy===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-group"><label for="sort_direction">{{ __('ui.arah_susunan.21d8cbc0') }}</label><select class="select" id="sort_direction" name="sort_direction"><option value="asc" @selected($sortDirection==='asc')>{{ __('ui.menaik.e5d9477d') }}</option><option value="desc" @selected($sortDirection==='desc')>{{ __('ui.menurun.d232111a') }}</option></select></div>
                <div class="form-group"><label for="per_page">{{ __('ui.rekod_setiap_halaman.8cd8d328') }}</label><select class="select" id="per_page" name="per_page">@foreach([10,15,25,50] as $value)<option value="{{ $value }}" @selected(($filters['per_page'] ?? 15)==$value)>{{ $value }}</option>@endforeach</select></div>
            </div>
        </details>
    </form>
</section>

<div class="result-summary" role="status" aria-live="polite">
    <span>
        <strong>{{ number_format($okus->total()) }}</strong> rekod ditemui
        @if($hasFilters)
            berdasarkan penapis semasa
        @endif
        .
    </span>
    @if($hasFilters)
        <a href="{{ route('oku.index') }}">{{ __('ui.kosongkan_semua_penapis.bd71bc35') }}</a>
    @endif
</div>

<section class="panel oku-table-panel">
    <div class="table-wrap">
        <table class="data-table oku-record-table">
            <caption class="sr-only">{{ __('ui.senarai_rekod_individu_oku_berdaftar.f00f4636') }}</caption>
            <thead><tr>
                <th scope="col" aria-sort="{{ $sortAria('name') }}"><a href="{{ $sortUrl('name') }}">{{ __('ui.nama.79d12c10') }} <span aria-hidden="true">↕</span></a></th>
                <th scope="col" aria-sort="{{ $sortAria('ic_number') }}"><a href="{{ $sortUrl('ic_number') }}">{{ __('ui.nombor_ic.b06741d0') }} <span aria-hidden="true">↕</span></a></th>
                <th scope="col" aria-sort="{{ $sortAria('oku_category') }}"><a href="{{ $sortUrl('oku_category') }}">{{ __('ui.kategori.b7964404') }} <span aria-hidden="true">↕</span></a></th>
                <th scope="col" aria-sort="{{ $sortAria('employment_status') }}"><a href="{{ $sortUrl('employment_status') }}">{{ __('ui.status_pekerjaan.9fa9d719') }} <span aria-hidden="true">↕</span></a></th>
                <th scope="col">{{ __('ui.pengesahan.916e3638') }}</th>
                <th scope="col" aria-sort="{{ $sortAria('age') }}"><a href="{{ $sortUrl('age') }}">{{ __('ui.umur.133cb71a') }} <span aria-hidden="true">↕</span></a></th>
                <th scope="col"><span class="sr-only">{{ __('ui.tindakan.4c20e744') }}</span></th>
            </tr></thead>
            <tbody>
            @forelse($okus as $oku)
                <tr>
                    <td data-label="Nama"><a class="record-name" href="{{ route('oku.show',$oku) }}">{{ $oku->name }}</a><small>{{ $oku->oku_card_number }}</small></td>
                    <td data-label="Nombor IC">{{ $oku->ic_number }}</td>
                    <td data-label="Kategori"><span class="badge">{{ $oku->oku_category }}</span></td>
                    <td data-label="Status Pekerjaan">{{ $oku->employment_status }}</td>
                    <td data-label="Pengesahan"><span class="verification-badge {{ strtolower($oku->verification_status) }}"><i aria-hidden="true"></i>{{ match($oku->verification_status) {'Verified'=>'Disahkan','Rejected'=>'Ditolak',default=>'Menunggu'} }}</span></td>
                    <td data-label="Umur">{{ $oku->age }} tahun</td>
                    <td data-label="Tindakan"><div class="table-actions"><a href="{{ route('oku.show',$oku) }}" aria-label="Lihat rekod {{ $oku->name }}">Lihat</a><a href="{{ route('oku.edit',$oku) }}" aria-label="Sunting rekod {{ $oku->name }}">Sunting</a></div></td>
                </tr>
            @empty
                <tr>
                    <td class="empty table-empty" colspan="7" role="status">
                        <span aria-hidden="true">⌕</span>
                        <strong>{{ __('ui.tiada_rekod_ditemui.8d0a21cd') }}</strong>
                        <p>{{ __('ui.cuba_ubah_carian_atau_kosongkan_penapis_semasa.0c991413') }}</p>
                        @if($hasFilters)
                            <a class="btn" href="{{ route('oku.index') }}">{{ __('ui.kosongkan_penapis.3bca173e') }}</a>
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($okus->hasPages())
    <nav class="pagination" aria-label="{{ __('ui.navigasi_halaman_rekod_oku.5073ff95') }}">{{ $okus->links('components.pagination') }}</nav>
@endif
@endsection
