@extends('layout',['title'=>$oku->exists?'Kemaskini OKU':'Daftar OKU'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Pengurusan OKU</p><h2>{{ $oku->exists?'Kemaskini Rekod':'Daftar Rekod Baharu' }}</h2><p>Pastikan maklumat peribadi dan kategori OKU diisi dengan tepat.</p></div><a class="btn" href="{{ route('oku.index') }}">← Kembali</a></div>
@if($errors->any())<div class="error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<form class="panel form-grid" method="post" action="{{ $oku->exists?route('oku.update',$oku):route('oku.store') }}">@csrf @if($oku->exists)@method('PUT')@endif
@foreach(['name'=>'Nama penuh','ic_number'=>'Nombor kad pengenalan','age'=>'Umur','address'=>'Alamat','education_level'=>'Tahap pendidikan','oku_card_number'=>'Nombor kad OKU','phone_number'=>'Nombor telefon','email'=>'E-mel'] as $n=>$l)<div class="form-group {{ $n==='address'?'full':'' }}"><label for="{{ $n }}">{{ $l }}</label><input class="field" id="{{ $n }}" name="{{ $n }}" value="{{ old($n,$oku->$n) }}" {{ in_array($n,['name','ic_number','age','address','education_level','oku_card_number'])?'required':'' }}></div>@endforeach
@foreach(['gender'=>['Lelaki','Perempuan'],'marital_status'=>['Berkahwin','Bujang','Duda','Janda'],'oku_category'=>['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'],'employment_status'=>['Bekerja','Tidak Bekerja','Sendiri']] as $n=>$opts)<div class="form-group"><label for="{{ $n }}">{{ ucwords(str_replace('_',' ',$n)) }}</label><select class="select" id="{{ $n }}" name="{{ $n }}">@foreach($opts as $o)<option @selected(old($n,$oku->$n)===$o)>{{ $o }}</option>@endforeach</select></div>@endforeach
<div class="full"><button class="btn btn-primary">Simpan Rekod</button></div></form>
@endsection
