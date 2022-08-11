<div class="mt-3">
    @if($data->count())
        {{ $data->total() }} 件中 {{ ($data->currentPage() - 1) * $data->perPage() + 1 }} 件から {{ ($data->currentPage() - 1) * $data->perPage() + $data->count() }} 件までを表示
    @else
        0 件中 0 件から 0 件までを表示
    @endif
</div>
<div class="text-center">@if($data->count()){{ $data->links() }}@endif</div>
