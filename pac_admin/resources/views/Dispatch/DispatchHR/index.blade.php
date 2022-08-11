@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_geteditdata = "{{ route('dispatchhr.geteditdata') }}",  
        link_deletes = "{{ route('dispatchhr.deletes') }}",
        link_save = "{{ route('dispatchhr.save') }}",
        link_savesetting = "{{ route('dispatchhr.savesetting') }}",
        link_geteditjobcareer = "{{ route('dispatchhr.geteditjobcareer') }}",
        link_savejobcareer = "{{ route('dispatchhr.savejobcareer') }}",
        link_deletejobcareer = "{{ route('dispatchhr.deletejobcareer') }}",
        link_user_search = "{{ route('dispatchhr.getuser') }}";    
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};     
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Dispatch.DispatchHR.list')

        @include('Dispatch.DispatchHR.detail')

    </div>

@endsection