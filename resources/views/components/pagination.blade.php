@if ($paginator->hasPages())
    <div class="pagination-shell">
        @if ($paginator->onFirstPage())
            <span class="pagination-button disabled" aria-disabled="true">{{ __('ui.sebelum.9a8474d9') }}</span>
        @else
            <a class="pagination-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('ui.sebelum.9a8474d9') }}</a>
        @endif

        <span class="pagination-status">{{ __('ui.halaman.8050d4cb') }} {{ $paginator->currentPage() }} {{ __('ui.daripada.fb3c9880') }} {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="pagination-button" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('ui.seterusnya.a15710d5') }}</a>
        @else
            <span class="pagination-button disabled" aria-disabled="true">{{ __('ui.seterusnya.a15710d5') }}</span>
        @endif
    </div>
@endif
