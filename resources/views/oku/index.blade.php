@extends('layout',['title'=>'Rekod OKU'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Pengurusan OKU</p><h2>Senarai Rekod OKU</h2><p>Semak, cari dan urus profil individu berdaftar.</p></div><a class="btn btn-primary" href="{{ route('oku.create') }}">＋ Daftar OKU</a></div>
<section class="panel">
<form class="toolbar"><input class="field" name="search" value="{{ request('search') }}" placeholder="Cari nama, nombor IC atau kad OKU"><button class="btn btn-primary">Cari Rekod</button></form>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Nama</th><th>Nombor IC</th><th>Kategori</th><th>Status Pekerjaan</th><th>Umur</th></tr></thead><tbody>
@forelse($okus as $oku)<tr><td><a href="{{ route('oku.show',$oku) }}">{{ $oku->name }}</a></td><td>{{ $oku->ic_number }}</td><td><span class="badge">{{ $oku->oku_category }}</span></td><td>{{ $oku->employment_status }}</td><td>{{ $oku->age }} tahun</td></tr>@empty<tr><td class="empty" colspan="5">Tiada rekod ditemui.</td></tr>@endforelse
</tbody></table></div></section><div class="pagination">{{ $okus->links() }}</div>
@endsection
