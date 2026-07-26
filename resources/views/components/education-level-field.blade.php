@props([
    'value' => '',
    'label' => 'Pendidikan tertinggi',
    'required' => true,
    'requiredLabel' => null,
    'error' => null,
    'selectClass' => '',
])

@php
    $options = [
        'Tiada Pendidikan Formal',
        'Pendidikan Prasekolah',
        'Sekolah Rendah / UPSR',
        'PMR / SRP / PT3',
        'SPM / SPMV',
        'STPM',
        'STAM',
        'Sijil Kemahiran Malaysia (SKM) Tahap 1',
        'Sijil Kemahiran Malaysia (SKM) Tahap 2',
        'Sijil Kemahiran Malaysia (SKM) Tahap 3',
        'Diploma Kemahiran Malaysia (DKM) Tahap 4',
        'Diploma Lanjutan Kemahiran Malaysia (DLKM) Tahap 5',
        'Sijil',
        'Diploma',
        'Diploma Lanjutan',
        'Ijazah Sarjana Muda',
        'Ijazah Sarjana',
        'Ijazah Kedoktoran (PhD)',
        'PPDK-PUSAT PEMULIHAN DALAM KOMUNITI',
        'PPKI-PROGRAM PENDIDIKAN KHAS INTEGRASI',
    ];
    $currentValue = (string) $value;
    $isOther = $currentValue !== '' && !in_array($currentValue, $options, true);
@endphp

<div class="form-group" data-education-level-field>
    <label for="education_level_choice">
        {{ $label }}
        @if($requiredLabel)
            {!! $requiredLabel !!}
        @endif
    </label>
    <select
        class="select {{ $selectClass }} @if($error) is-invalid @endif"
        id="education_level_choice"
        data-education-level-choice
        @if($required) required aria-required="true" @endif
        @if($error) aria-invalid="true" @endif
    >
        <option value="">Pilih taraf pendidikan</option>
        @foreach($options as $option)
            <option value="{{ $option }}" @selected($currentValue === $option)>{{ $option }}</option>
        @endforeach
        <option value="Lain-lain" @selected($isOther)>Lain-lain</option>
    </select>

    <div data-education-level-other-wrap @if(!$isOther) hidden @endif style="margin-top: 10px">
        <label for="education_level_other">Nyatakan taraf pendidikan</label>
        <input
            class="field"
            id="education_level_other"
            data-education-level-other
            value="{{ $isOther ? $currentValue : '' }}"
            maxlength="100"
            placeholder="Sila nyatakan taraf pendidikan"
            @if($isOther && $required) required aria-required="true" @endif
        >
    </div>

    <input type="hidden" id="education_level" name="education_level" value="{{ $currentValue }}" data-education-level-value>
    @if($error)
        <span class="field-error" id="education_level-error" role="alert">{{ $error }}</span>
    @endif
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-education-level-field]').forEach(function (field) {
                var choice = field.querySelector('[data-education-level-choice]');
                var otherWrap = field.querySelector('[data-education-level-other-wrap]');
                var other = field.querySelector('[data-education-level-other]');
                var value = field.querySelector('[data-education-level-value]');

                function syncEducationLevel() {
                    var usesOther = choice.value === 'Lain-lain';
                    otherWrap.hidden = !usesOther;
                    other.required = usesOther && choice.required;
                    value.value = usesOther ? other.value.trim() : choice.value;
                }

                choice.addEventListener('change', syncEducationLevel);
                other.addEventListener('input', syncEducationLevel);
                syncEducationLevel();
            });
        });
    </script>
@endonce
