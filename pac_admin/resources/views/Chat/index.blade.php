@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            hasChange = false,
            link_ajax = "{{ url('chat/management-user') }}",
            link_bulk_usage = "{{ url('chat/management-user/bulk-usage') }}";

    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Chat.list')
        @include('Chat.detail')
    </div>

@endsection
