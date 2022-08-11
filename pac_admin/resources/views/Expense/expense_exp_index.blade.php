@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/m_form_exp') }}",
            link_bulk_usage = "{{ url('expense/m_form_exp/delete') }}";
        var link_ajax_csv = "{{ route('CsvExpenseMFormExp') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.expense_exp_list')
        @include('Expense.expense_exp_detail')
    </div>
    
@endsection
