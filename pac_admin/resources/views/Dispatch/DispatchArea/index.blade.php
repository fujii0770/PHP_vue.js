@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_agency_deletes = "{{ route('dispatcharea.agencydeletes') }}",
        link_dispatcharea_deletes = "{{ route('dispatcharea.dispatchareadeletes') }}",
        link_agency_save = "{{ route('dispatcharea.agencysave') }}",
        link_dispatcharea_save = "{{ route('dispatcharea.dispatchareasave') }}",
        link_agency_search = "{{ route('dispatcharea.getagency') }}";
        link_dispatcharea_geteditdata = "{{ route('dispatcharea.geteditdata') }}";         
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};     
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Dispatch.DispatchArea.list')

        @include('Dispatch.DispatchArea.detail')

    </div>

@endsection