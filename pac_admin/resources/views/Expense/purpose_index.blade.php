@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('expense/m_purpose') }}",
            link_ajax_journal = "{{ url('expense/m_journal_config') }}",
            link_bulk_usage = "{{ url('expense/m_purpose/bulk-usage') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Expense.purpose_list')
        @include('Expense.purpose_detail')
    </div>

@endsection
