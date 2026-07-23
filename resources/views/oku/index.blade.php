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
        <p class="eyebrow">Pengurusan OKU</p>
        <h2>Senarai Rekod OKU</h2>
        <p>Semak, cari dan urus profil individu berdaftar.</p>
    </div>
    <div class="page-actions">
        <a class="btn" href="{{ route('reports.export','oku') }}">Eksport CSV</a>
        <a class="btn btn-primary" href="{{ route('oku.create') }}">Daftar OKU</a>
    </div>
</div>

<section class="oku-summary-grid" aria-label="Ringkasan rekod OKU">
    @foreach([
        ['tone'=>'coral','icon'=>'id-card','value'=>$stats['total'],'label'=>'Jumlah rekod'],
        ['tone'=>'green','icon'=>'briefcase','value'=>$stats['employed'],'label'=>'Sudah bekerja'],
        ['tone'=>'amber','icon'=>'job-search','value'=>$stats['unemployed'],'label'=>'Belum bekerja'],
        ['tone'=>'purple','icon'=>'welfare','value'=>$stats['pending_verification'],'label'=>'Menunggu pengesahan'],
    ] as $summary)
        <article class="oku-summary-card {{ $summary['tone'] }}">
            <span class="oku-summary-icon" aria-hidden="true"><x-dashboard-icon :name="$summary['icon']" /></span>
            <div><span>{{ $summary['label'] }}</span><strong>{{ number_format($summary['value']) }}</strong><small>Data keseluruhan</small></div>
        </article>
    @endforeach
</section>

@if($errors->any())
    <div class="error" role="alert">{{ $errors->first() }}</div>
@endif

<section class="panel oku-filter-panel">
    <div class="oku-filter-head">
        <div><h3>Cari dan Tapis Rekod</h3><p>Gunakan satu atau beberapa penapis untuk mengecilkan hasil carian.</p></div>
        @if($hasFilters)<a href="{{ route('oku.index') }}">Kosongkan semua</a>@endif
    </div>
    <form method="get" action="{{ route('oku.index') }}" role="search" aria-label="Cari dan tapis rekod OKU">
        <div class="filter-primary">
            <div class="form-group filter-search">
                <label for="search">Carian rekod</label>
                <input class="field" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, nombor IC atau nombor Kad OKU">
            </div>
            <div class="form-group">
                <label for="category">Kategori</label>
                <select class="select" id="category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $value)<option @selected(($filters['category'] ?? '')===$value)>{{ $value }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="employment_status">Status pekerjaan</label>
                <select class="select" id="employment_status" name="employment_status">
                    <option value="">Semua status</option>
                    @foreach(['Bekerja','Tidak Bekerja','Sendiri'] as $value)<option @selected(($filters['employment_status'] ?? '')===$value)>{{ $value }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="verification_status">Pengesahan Kad OKU</label>
                <select class="select" id="verification_status" name="verification_status">
                    <option value="">Semua pengesahan</option>
                    @foreach(['Pending'=>'Menunggu','Verified'=>'Disahkan','Rejected'=>'Ditolak'] as $value=>$label)
                        <option value="{{ $value }}" @selected(($filters['verification_status'] ?? '')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary filter-submit" type="submit">Tapis Rekod</button>
        </div>
        <details class="advanced-filters" @if(isset($filters['age_min']) || isset($filters['age_max'])) open @endif>
            <summary>Penapis lanjutan</summary>
            <div class="advanced-filter-grid">
                <div class="form-group"><label for="age_min">Umur minimum</label><input class="field" id="age_min" name="age_min" type="number" min="1" max="120" value="{{ $filters['age_min'] ?? '' }}"></div>
                <div class="form-group"><label for="age_max">Umur maksimum</label><input class="field" id="age_max" name="age_max" type="number" min="1" max="120" value="{{ $filters['age_max'] ?? '' }}"></div>
                <div class="form-group"><label for="sort_by">Susun mengikut</label><select class="select" id="sort_by" name="sort_by">@foreach(['created_at'=>'Rekod terkini','name'=>'Nama','ic_number'=>'Nombor IC','oku_category'=>'Kategori','employment_status'=>'Status pekerjaan','age'=>'Umur'] as $key=>$label)<option value="{{ $key }}" @selected($sortBy===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-group"><label for="sort_direction">Arah susunan</label><select class="select" id="sort_direction" name="sort_direction"><option value="asc" @selected($sortDirection==='asc')>Menaik</option><option value="desc" @selected($sortDirection==='desc')>Menurun</option></select></div>
                <div class="form-group"><label for="per_page">Rekod setiap halaman</label><select class="select" id="per_page" name="per_page">@foreach([10,15,25,50] as $value)<option value="{{ $value }}" @selected(($filters['per_page'] ?? 15)==$value)>{{ $value }}</option>@endforeach</select></div>
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
        <a href="{{ route('oku.index') }}">Kosongkan semua penapis</a>
    @endif
</div>

<section class="panel oku-table-panel">
    <div class="table-wrap">
        <table class="data-table oku-record-table">
            <caption class="sr-only">Senarai rekod individu OKU berdaftar</caption>
            <thead><tr>
                <th scope="col" aria-sort="{{ $sortAria('name') }}"><a href="{{ $sortUrl('name') }}">Nama <span aria-hidden="true">↕</span></a></th>
                <th scope="col" aria-sort="{{ $sortAria('ic_number') }}"><a href="{{ $sortUrl('ic_number') }}">Nombor IC <span aria-hidden="true">↕</span></a></th>
                <th scope="col" aria-sort="{{ $sortAria('oku_category') }}"><a href="{{ $sortUrl('oku_category') }}">Kategori <span aria-hidden="true">↕</span></a></th>
                <th scope="col" aria-sort="{{ $sortAria('employment_status') }}"><a href="{{ $sortUrl('employment_status') }}">Status Pekerjaan <span aria-hidden="true">↕</span></a></th>
                <th scope="col">Pengesahan</th>
                <th scope="col" aria-sort="{{ $sortAria('age') }}"><a href="{{ $sortUrl('age') }}">Umur <span aria-hidden="true">↕</span></a></th>
                <th scope="col"><span class="sr-only">Tindakan</span></th>
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
                        <strong>Tiada rekod ditemui</strong>
                        <p>Cuba ubah carian atau kosongkan penapis semasa.</p>
                        @if($hasFilters)
                            <a class="btn" href="{{ route('oku.index') }}">Kosongkan Penapis</a>
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($okus->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman rekod OKU">{{ $okus->links('components.pagination') }}</nav>
@endif
@endsection
