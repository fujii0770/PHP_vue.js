@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_update = "{{ action('Special\SpecialReceiveController@update') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Special.Receive.list')
    </div>

@endsection
