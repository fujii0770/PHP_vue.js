@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/m_account') }}",
            link_bulk_usage = "{{ url('expense/m_account/bulk-usage') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.account_list')
        @include('Expense.account_detail')
    </div>

@endsection
