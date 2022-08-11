@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('bizcards') }}",
        link_deletes = "{{ route('bizcards.deletes') }}";
        var hasChange = false;
    </script>
@endpush

@section('content')
    <span class="clear"></span>
    <div class="Bizcards">
        @include('Bizcards.list')
        @include('Bizcards.displaysetting')
    </div>
@endsection