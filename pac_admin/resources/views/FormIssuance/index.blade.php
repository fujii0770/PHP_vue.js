@extends('layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('form-issuance/user-register') }}",
            link_bulk_usage = "{{ url('form-issuance/user-register/bulk-usage') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('FormIssuance.list')
        @include('FormIssuance.detail')
    </div>

@endsection
