@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/m_form_adv') }}",
            link_show = "{{ url('m_form_adv_show') }}";
            link_bulk_usage = "{{ url('expense/m_form_adv/delete') }}";
        var link_ajax_csv = "{{ route('CsvExpenseMFormAdv') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.expense_list')
        @include('Expense.expense_detail')
    </div>
    
@endsection
