@if ($paginator->hasPages())
    <div class="pagination-shell">
        @if ($paginator->onFirstPage())
            <span class="pagination-button disabled" aria-disabled="true">Sebelum</span>
        @else
            <a class="pagination-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelum</a>
        @endif

        <span class="pagination-status">Halaman {{ $paginator->currentPage() }} daripada {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="pagination-button" href="{{ $paginator->nextPageUrl() }}" rel="next">Seterusnya</a>
        @else
            <span class="pagination-button disabled" aria-disabled="true">Seterusnya</span>
        @endif
    </div>
@endif
