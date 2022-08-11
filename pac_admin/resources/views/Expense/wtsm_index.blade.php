@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/m_wtsm') }}",
            link_bulk_usage = "{{ url('expense/m_wtsm/bulk-usage') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.wtsm_list')
        @include('Expense.wtsm_detail')
    </div>

@endsection
