@extends('layout', ['title' => 'Semakan Identiti'])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.pengesahan_identiti.a58b0502') }}</p><h2>{{ __('ui.semakan_manual.51918cc4') }}</h2><p>{{ __('ui.semak_permohonan_yang_tidak_dapat_diluluskan_secara.e6d4ac7f') }}</p></div></div>
<section class="panel table-panel"><div class="table-wrap"><table class="data-table mobile-card-table"><thead><tr><th>{{ __('ui.pengguna.c720f761') }}</th><th>{{ __('ui.sebab.f0949952') }}</th><th>{{ __('ui.status.bae7d5be') }}</th><th>{{ __('ui.tarikh.bb81283e') }}</th><th></th></tr></thead><tbody>
@forelse($reviews as $review)<tr><td data-label="{{ __('ui.pengguna.c720f761') }}">{{ $review->session->user->name }}</td><td data-label="{{ __('ui.sebab.f0949952') }}">{{ implode(', ', $review->reason_codes) }}</td><td data-label="{{ __('ui.status.bae7d5be') }}"><span class="status">{{ $review->status }}</span></td><td data-label="{{ __('ui.tarikh.bb81283e') }}">{{ $review->created_at->format('d/m/Y H:i') }}</td><td data-label="{{ __('ui.tindakan.4c20e744') }}"><a class="btn" href="{{ route('identity-reviews.show', $review) }}">{{ __('ui.semak.621e8157') }}</a></td></tr>
@empty<tr><td colspan="5"><div class="empty">{{ __('ui.tiada_rekod_semakan_manual.8bb5f5bf') }}</div></td></tr>@endforelse
</tbody></table></div></section>{{ $reviews->links() }}
@endsection
