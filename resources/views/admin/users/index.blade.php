@php
$roleLabels=['super_admin'=>'Admin System','jkm_officer'=>'Pegawai JKM','employer'=>'Majikan','oku_user'=>'Pengguna OKU'];
$pageRole=$pageRole??null;
$heading=$pageRole?'Pengguna '.$roleLabels[$pageRole]:'Pengurusan Pengguna';
$filterAction=$pageRole?route('admin.users.role',$pageRole):route('admin.users.index');
@endphp
@extends('layout',['title'=>$heading])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.pentadbiran_sistem.f78f3faf') }}</p><h2>{{ $heading }}</h2><p>{{ $pageRole?'Urus akaun dan status akses untuk peranan '.$roleLabels[$pageRole].'.':'Urus akaun, peranan, pautan profil dan status akses sistem.' }}</p></div><a class="btn btn-primary" href="{{ route('admin.users.create',['role'=>$pageRole]) }}">Daftar Pengguna</a></div>
<section class="user-stat-grid" aria-label="{{ __('ui.ringkasan_pengguna.cf60ff57') }}">
@foreach([[$pageRole?'Jumlah '.$roleLabels[$pageRole]:'Jumlah akaun',$stats['total'],'total'],['Akaun aktif',$stats['active'],'active'],['Profil dipautkan',$stats['linked'],'staff'],['Tidak aktif',$stats['inactive'],'inactive']] as [$label,$value,$tone])
<article class="panel user-stat {{ $tone }}"><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>
@endforeach
</section>
<form class="panel user-filter {{ $pageRole?'role-locked':'' }}" method="get" action="{{ $filterAction }}" role="search">
    <div class="form-group user-search"><label for="user-search">{{ __('ui.cari_pengguna.02f2f4c5') }}</label><input class="field" id="user-search" name="search" type="search" maxlength="100" value="{{ $filters['search']??'' }}" placeholder="{{ __('ui.nama_atau_alamat_e_mel.4ecfbf06') }}"></div>
    @unless($pageRole)<div class="form-group"><label for="user-role">{{ __('ui.peranan.0ef21dad') }}</label><select class="select" id="user-role" name="role"><option value="">{{ __('ui.semua_peranan.d7bfeb35') }}</option>@foreach($roleLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['role']??'')===$value)>{{ $label }}</option>@endforeach</select></div>@endunless
    <div class="form-group"><label for="user-status">{{ __('ui.status.bae7d5be') }}</label><select class="select" id="user-status" name="status"><option value="">{{ __('ui.semua_status.baa2adda') }}</option><option value="active" @selected(($filters['status']??'')==='active')>{{ __('ui.aktif.89f29d42') }}</option><option value="inactive" @selected(($filters['status']??'')==='inactive')>{{ __('ui.tidak_aktif.c5f1e8e2') }}</option></select></div>
    <button class="btn btn-primary" type="submit">{{ __('ui.tapis.6d4dc681') }}</button>@if(request()->query())<a class="btn" href="{{ $filterAction }}">{{ __('ui.kosongkan.899f41b5') }}</a>@endif
</form>
<div class="result-summary"><span><strong>{{ $users->total() }}</strong> {{ __('ui.akaun_ditemui.14d0a0fc') }}</span><a href="{{ route('admin.audit') }}">{{ __('ui.lihat_audit_aktiviti.d79009ad') }}</a></div>
<section class="panel user-table-panel"><div class="table-wrap"><table class="data-table user-table"><thead><tr><th>{{ __('ui.pengguna.c720f761') }}</th><th>{{ __('ui.peranan.0ef21dad') }}</th><th>{{ __('ui.profil_dipautkan.8781ee05') }}</th><th>{{ __('ui.log_masuk_terakhir.09b0af44') }}</th><th>{{ __('ui.status.bae7d5be') }}</th><th><span class="sr-only">{{ __('ui.tindakan.4c20e744') }}</span></th></tr></thead><tbody>
@forelse($users as $managedUser)<tr>
<td data-label="Pengguna"><strong>{{ $managedUser->name }}</strong><small>{{ $managedUser->email }}</small></td>
<td data-label="Peranan"><span class="role-pill role-{{ $managedUser->role }}">{{ $roleLabels[$managedUser->role] }}</span></td>
<td data-label="Profil">@if($managedUser->employer)<strong>{{ $managedUser->employer->company_name }}</strong>@elseif($managedUser->oku)<strong>{{ $managedUser->oku->name }}</strong><small>{{ $managedUser->oku->oku_card_number }}</small>@else<span class="muted-value">{{ __('ui.tiada_pautan.85cfb9ac') }}</span>@endif</td>
<td data-label="Log masuk">{{ $managedUser->last_login_at?->format('d/m/Y H:i')??'Belum pernah' }}</td>
<td data-label="Status"><span class="status-badge {{ $managedUser->is_active?'is-active':'is-inactive' }}"><span></span>{{ $managedUser->is_active?'Aktif':'Tidak aktif' }}</span></td>
<td data-label="Tindakan"><div class="table-actions"><a href="{{ route('admin.users.edit',$managedUser) }}">{{ __('ui.sunting.b7b0d4ed') }}</a><form class="inline-form" method="post" action="{{ route('admin.users.destroy',$managedUser) }}" onsubmit="return confirm('Padamkan akaun {{ $managedUser->name }}? Tindakan ini tidak boleh dibatalkan.')">@csrf @method('DELETE')<button type="submit" class="btn-link danger">{{ __('ui.padam.8f2d5bce') }}</button></form></div></td>
</tr>@empty<tr><td class="empty" colspan="6">{{ __('ui.tiada_akaun_pengguna_ditemui.7e3867eb') }}</td></tr>@endforelse
</tbody></table></div></section>
<div class="pagination">{{ $users->links('components.pagination') }}</div>
@endsection
