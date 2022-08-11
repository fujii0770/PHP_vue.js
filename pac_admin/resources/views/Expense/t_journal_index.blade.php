@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ url('expense/t_journal') }}";
        var link_ajax_reserve = "{{ url('expense/t_app') }}";
        var link_ajax_csv = "{{ route('CsvJournalList') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.t_journal_list')

        @include('Expense.t_journal_detail')
    </div>

@endsection
