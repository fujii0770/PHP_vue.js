@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ url('expense/t_app') }}";
        var link_ajax_detail2 = "{{ url('expense/t_app/detail2') }}";
        var link_ajax_csv = "{{ route('CsvExpenseAppList') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.app_list')

        @include('Expense.app_detail')

        @include('Expense.app_detail2')
    </div>

@endsection
