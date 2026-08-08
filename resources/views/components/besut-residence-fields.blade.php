@props(['oku' => null, 'required' => true])
@php
    $mukim = old('residential_mukim', $oku?->residential_mukim);
    $village = old('residential_village', $oku?->residential_village);
    $postcode = old('residential_postcode', $oku?->residential_postcode);
    $besutOnly = app(\App\Services\FeatureManager::class)->besutOnlyLocationScopeEnabled();
    $state = old('residential_state', $oku?->residential_state ?? config('besut.state'));
    $district = old('residential_district', $oku?->residential_district ?? config('besut.district'));
@endphp
@if($besutOnly)
<div class="form-group">
    <label for="residential_state">Negeri</label>
    <input class="field" id="residential_state" value="{{ config('besut.state') }}" readonly aria-readonly="true">
</div>
<div class="form-group">
    <label for="residential_district">Daerah</label>
    <input class="field" id="residential_district" value="{{ config('besut.district') }}" readonly aria-readonly="true">
</div>
@else
<div class="form-group">
    <label for="residential_state">Negeri <span class="required-mark">*</span></label>
    <select class="select oku-required @error('residential_state') is-invalid @enderror" id="residential_state" name="residential_state" required>
        <option value="">Pilih negeri</option>
        @foreach(config('besut.states') as $option)
            <option value="{{ $option }}" @selected($state === $option)>{{ $option }}</option>
        @endforeach
    </select>
    @error('residential_state')<span class="field-error">{{ $message }}</span>@enderror
</div>
<div class="form-group">
    <label for="residential_district">Daerah <span class="required-mark">*</span></label>
    <input class="field oku-required @error('residential_district') is-invalid @enderror" id="residential_district" name="residential_district" value="{{ $district }}" maxlength="100" required>
    @error('residential_district')<span class="field-error">{{ $message }}</span>@enderror
</div>
@endif
<div class="form-group">
    <label for="residential_mukim">Mukim kediaman @if($required && $besutOnly)<span class="required-mark">*</span>@endif</label>
    @if($besutOnly)
    <select class="select oku-required @error('residential_mukim') is-invalid @enderror" id="residential_mukim" name="residential_mukim" @required($required) @error('residential_mukim') aria-invalid="true" aria-describedby="residential_mukim-error" @enderror>
        <option value="">Pilih mukim dalam Daerah Besut</option>
        @foreach(config('besut.mukims') as $option)
            <option value="{{ $option }}" @selected($mukim === $option)>{{ $option }}</option>
        @endforeach
    </select>
    @else
    <input class="field @error('residential_mukim') is-invalid @enderror" id="residential_mukim" name="residential_mukim" value="{{ $mukim }}" maxlength="100" list="besut-mukim-options">
    <datalist id="besut-mukim-options">@foreach(config('besut.mukims') as $option)<option value="{{ $option }}">@endforeach</datalist>
    <small class="field-help">Wajib dan disemak dengan senarai rasmi apabila negeri Terengganu dan daerah Besut dipilih.</small>
    @endif
    @error('residential_mukim')<span class="field-error" id="residential_mukim-error">{{ $message }}</span>@enderror
</div>
<div class="form-group">
    <label for="residential_village">Kampung / kawasan @if($required)<span class="required-mark">*</span>@endif</label>
    <input class="field oku-required @error('residential_village') is-invalid @enderror" id="residential_village" name="residential_village" value="{{ $village }}" maxlength="255" @required($required) @error('residential_village') aria-invalid="true" aria-describedby="residential_village-error" @enderror>
    @error('residential_village')<span class="field-error" id="residential_village-error">{{ $message }}</span>@enderror
</div>
<div class="form-group">
    <label for="residential_postcode">Poskod @if($required)<span class="required-mark">*</span>@endif</label>
    <input class="field oku-required @error('residential_postcode') is-invalid @enderror" id="residential_postcode" name="residential_postcode" value="{{ $postcode }}" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" @required($required) @error('residential_postcode') aria-invalid="true" aria-describedby="residential_postcode-error" @enderror>
    @error('residential_postcode')<span class="field-error" id="residential_postcode-error">{{ $message }}</span>@enderror
</div>
<div class="form-group location-scope-note">
    <strong>{{ $besutOnly ? 'Skop Daerah Besut' : 'Skop Seluruh Malaysia' }}</strong>
    <small>{{ $besutOnly ? 'Pilihan ini ialah pengisytiharan pengguna. Kediaman hanya dianggap disahkan selepas alamat pada Kad OKU disemak oleh pegawai JKM.' : 'Negeri dan daerah boleh dipilih. Pengesahan mukim melalui Kad OKU hanya digunakan untuk rekod dalam Daerah Besut.' }}</small>
</div>
