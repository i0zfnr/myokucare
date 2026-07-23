@extends('layout',['title'=>'Pengurusan Pengguna'])
@section('content')
@php $roleLabels=['super_admin'=>'Super Admin','jkm_officer'=>'Pegawai JKM','employer'=>'Majikan','oku_user'=>'Pengguna OKU','family_member'=>'Ahli Keluarga','viewer'=>'Viewer']; @endphp
<div class="page-head"><div><p class="eyebrow">Pentadbiran Sistem</p><h2>Pengurusan Pengguna</h2><p>Urus akaun, peranan, pautan profil dan status akses sistem.</p></div><a class="btn btn-primary" href="{{ route('admin.users.create') }}">Daftar Pengguna</a></div>
<section class="user-stat-grid" aria-label="Ringkasan pengguna">
@foreach([['Jumlah akaun',$stats['total'],'total'],['Akaun aktif',$stats['active'],'active'],['Kakitangan',$stats['staff'],'staff'],['Tidak aktif',$stats['inactive'],'inactive']] as [$label,$value,$tone])
<article class="panel user-stat {{ $tone }}"><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>
@endforeach
</section>
<form class="panel user-filter" method="get" action="{{ route('admin.users.index') }}" role="search">
    <div class="form-group user-search"><label for="user-search">Cari pengguna</label><input class="field" id="user-search" name="search" type="search" maxlength="100" value="{{ $filters['search']??'' }}" placeholder="Nama atau alamat e-mel"></div>
    <div class="form-group"><label for="user-role">Peranan</label><select class="select" id="user-role" name="role"><option value="">Semua peranan</option>@foreach($roleLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['role']??'')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="form-group"><label for="user-status">Status</label><select class="select" id="user-status" name="status"><option value="">Semua status</option><option value="active" @selected(($filters['status']??'')==='active')>Aktif</option><option value="inactive" @selected(($filters['status']??'')==='inactive')>Tidak aktif</option></select></div>
    <button class="btn btn-primary" type="submit">Tapis</button>@if(request()->query())<a class="btn" href="{{ route('admin.users.index') }}">Kosongkan</a>@endif
</form>
<div class="result-summary"><span><strong>{{ $users->total() }}</strong> akaun ditemui</span><a href="{{ route('admin.audit') }}">Lihat audit aktiviti</a></div>
<section class="panel user-table-panel"><div class="table-wrap"><table class="data-table user-table"><thead><tr><th>Pengguna</th><th>Peranan</th><th>Profil dipautkan</th><th>Log masuk terakhir</th><th>Status</th><th><span class="sr-only">Tindakan</span></th></tr></thead><tbody>
@forelse($users as $managedUser)<tr>
<td data-label="Pengguna"><strong>{{ $managedUser->name }}</strong><small>{{ $managedUser->email }}</small></td>
<td data-label="Peranan"><span class="role-pill role-{{ $managedUser->role }}">{{ $roleLabels[$managedUser->role] }}</span></td>
<td data-label="Profil">@if($managedUser->employer)<strong>{{ $managedUser->employer->company_name }}</strong>@elseif($managedUser->oku)<strong>{{ $managedUser->oku->name }}</strong><small>{{ $managedUser->oku->oku_card_number }}</small>@else<span class="muted-value">Tiada pautan</span>@endif</td>
<td data-label="Log masuk">{{ $managedUser->last_login_at?->format('d/m/Y H:i')??'Belum pernah' }}</td>
<td data-label="Status"><span class="status-badge {{ $managedUser->is_active?'is-active':'is-inactive' }}"><span></span>{{ $managedUser->is_active?'Aktif':'Tidak aktif' }}</span></td>
<td data-label="Tindakan"><div class="table-actions"><a href="{{ route('admin.users.edit',$managedUser) }}">Sunting</a></div></td>
</tr>@empty<tr><td class="empty" colspan="6">Tiada akaun pengguna ditemui.</td></tr>@endforelse
</tbody></table></div></section>
<div class="pagination">{{ $users->links('components.pagination') }}</div>
@endsection
