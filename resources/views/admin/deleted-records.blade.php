@extends('layout',['title'=>'Rekod Dipadam'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Pentadbiran Data</p><h2>Rekod Dipadam</h2><p>Pulihkan rekod atau lakukan pemadaman kekal dengan kebenaran khas.</p></div></div>
@foreach(['Majikan'=>$employers,'Pengguna OKU'=>$okus] as $title=>$records)
<section class="panel" style="margin-bottom:18px"><div class="panel-head"><h3>{{ $title }}</h3></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Nama</th><th>Sebab</th><th>Tarikh</th><th>Tindakan</th></tr></thead><tbody>@forelse($records as $record)
@php($isEmployer=$record instanceof \App\Models\Employer)
<tr><td>{{ $record->company_name ?? $record->name }}</td><td>{{ $record->deletion_reason }}<small>{{ $record->deletion_notes }}</small></td><td>{{ $record->deleted_at?->format('d/m/Y H:i') }}</td><td><div class="page-actions">
@if(app(\App\Services\PermissionService::class)->allows(auth()->user(),'record.restore'))<form method="post" action="{{ $isEmployer?route('deleted-records.employers.restore',$record->id):route('deleted-records.okus.restore',$record->id) }}" onsubmit="return confirm('Restore record? This record will become active and accessible again.')">@csrf<input type="hidden" name="restore_reason" value="Semakan pentadbir"><button class="btn">Pulihkan</button></form>@endif
@if(app(\App\Services\PermissionService::class)->allows(auth()->user(),'record.permanent_delete'))<details><summary class="btn btn-danger">Padam kekal</summary><form method="post" action="{{ $isEmployer?route('deleted-records.employers.permanent',$record->id):route('deleted-records.okus.permanent',$record->id) }}">@csrf @method('DELETE')<p>Tindakan ini tidak boleh dibatalkan. Rekod berkaitan mesti diselesaikan dahulu.</p><input class="field" name="reason" placeholder="Sebab" required><input class="field" name="confirmation_text" placeholder="PERMANENTLY DELETE" required><input class="field" name="password" type="password" placeholder="Kata laluan semasa" required><button class="btn btn-danger">Permanently delete</button></form></details>@endif
</div></td></tr>@empty<tr><td colspan="4" class="empty">Tiada rekod dipadam.</td></tr>@endforelse</tbody></table></div></section>
@endforeach
<style>.btn-danger{background:#a61b29!important;color:#fff!important}details>form{position:absolute;z-index:3;background:white;padding:18px;border:1px solid #ddd;border-radius:12px;box-shadow:0 16px 40px #0003;width:min(360px,80vw)}details>form .field{margin-bottom:8px}</style>
@endsection
