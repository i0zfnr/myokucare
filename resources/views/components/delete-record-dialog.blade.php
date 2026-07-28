@props(['action', 'recordType', 'recordName', 'maskedIdentifier' => null, 'effect', 'permission'])
@if(app(\App\Services\PermissionService::class)->allows(auth()->user(), $permission))
@php($dialogId='delete-record-'.substr(md5($action),0,10))
<button type="button" class="btn delete-trigger" data-delete-open="{{ $dialogId }}">{{ __('ui.padam.8f2d5bce') }}</button>
<dialog id="{{ $dialogId }}" class="delete-dialog" aria-labelledby="{{ $dialogId }}-title">
<form method="post" action="{{ $action }}">@csrf @method('DELETE')
<h3 id="{{ $dialogId }}-title">Padam {{ $recordType }}?</h3>
<p>{{ __('ui.anda_akan_memadam.eab4297b') }} <strong>{{ $recordName }}</strong>@if($maskedIdentifier), {{ $maskedIdentifier }}@endif. {{ $effect }}</p>
<div class="form-group"><label>{{ __('ui.sebab_pemadaman.2c1ed866') }}</label><select class="select" name="reason" required><option value="">{{ __('ui.pilih_sebab.5fdb51bd') }}</option>@foreach(['Duplicate record','Incorrect registration','User request','Employer closed','Fraudulent record','Created by mistake','Data retention requirement','Other'] as $reason)<option>{{ $reason }}</option>@endforeach</select></div>
<div class="form-group"><label>{{ __('ui.penjelasan_pilihan.52211153') }}</label><textarea class="field" name="notes" maxlength="2000"></textarea></div>
<div class="form-group"><label>{{ __('ui.taip.6ae57156') }} <strong>DELETE</strong> {{ __('ui.untuk_mengesahkan.f09c99ea') }}</label><input class="field" name="confirmation_text" autocomplete="off" data-delete-confirm required></div>
<div class="form-actions"><button type="button" class="btn" data-delete-cancel autofocus>{{ __('ui.batal.1433539c') }}</button><button type="submit" class="btn btn-danger" data-delete-submit disabled>Padam {{ $recordType }}</button></div>
</form></dialog>
@once
<style>.delete-dialog{max-width:560px;width:calc(100% - 32px);border:0;border-radius:16px;padding:24px;box-shadow:0 24px 70px #0005}.delete-dialog::backdrop{background:#11182799}.btn-danger,.delete-trigger{background:#a61b29!important;color:#fff!important;border-color:#a61b29!important}</style>
<script>
document.addEventListener('click',event=>{const open=event.target.closest('[data-delete-open]');if(open)document.getElementById(open.dataset.deleteOpen)?.showModal();const cancel=event.target.closest('[data-delete-cancel]');if(cancel)cancel.closest('dialog').close()});
document.addEventListener('input',event=>{if(event.target.matches('[data-delete-confirm]'))event.target.closest('form').querySelector('[data-delete-submit]').disabled=event.target.value!=='DELETE'});
document.addEventListener('cancel',event=>{if(event.target.matches('.delete-dialog'))event.preventDefault()});
</script>
@endonce
@endif
