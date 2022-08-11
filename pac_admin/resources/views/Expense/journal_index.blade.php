@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/m_journal_config') }}",
            link_bulk_usage = "{{ url('expense/m_journal_config/bulk-usage') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.journal_list')
        @include('Expense.journal_detail')
    </div>

@endsection
