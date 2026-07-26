@extends('layout', ['title' => 'Semakan Identiti'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Pengesahan Identiti</p><h2>Semakan Manual</h2><p>Semak permohonan yang tidak dapat diluluskan secara automatik.</p></div></div>
<section class="panel table-panel"><div class="table-wrap"><table><thead><tr><th>Pengguna</th><th>Sebab</th><th>Status</th><th>Tarikh</th><th></th></tr></thead><tbody>
@forelse($reviews as $review)<tr><td>{{ $review->session->user->name }}</td><td>{{ implode(', ', $review->reason_codes) }}</td><td><span class="status">{{ $review->status }}</span></td><td>{{ $review->created_at->format('d/m/Y H:i') }}</td><td><a class="btn" href="{{ route('identity-reviews.show', $review) }}">Semak</a></td></tr>
@empty<tr><td colspan="5"><div class="empty">Tiada rekod semakan manual.</div></td></tr>@endforelse
</tbody></table></div></section>{{ $reviews->links() }}
@endsection
