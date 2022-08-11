@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/user-register') }}",
            link_bulk_usage = "{{ url('expense/user-register/bulk-usage') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.user_list')
        @include('Expense.user_detail')
    </div>

@endsection
