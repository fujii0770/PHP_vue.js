@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_update = "{{ action('Special\SpecialSendController@update') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Special.Send.list')
    </div>

@endsection
