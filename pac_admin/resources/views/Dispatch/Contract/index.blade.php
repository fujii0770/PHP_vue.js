@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_geteditdata = "{{ route('contract.geteditdata') }}",  
        link_deletes = "{{ route('contract.deletes') }}",
        link_save = "{{ route('contract.save') }}",
        link_dispatcharea_search = "{{ route('contract.getdispatcharea') }}";
        link_user_search = "{{ route('contract.getuser') }}";    
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};     
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Dispatch.Contract.list')

        @include('Dispatch.Contract.detail')

    </div>

@endsection