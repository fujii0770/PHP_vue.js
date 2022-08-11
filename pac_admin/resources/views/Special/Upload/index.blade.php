@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_update = "{{ action('Special\SpecialUploadController@update') }}";
        var link_upload = "{{ action('Special\SpecialUploadController@upload') }}";
        var link_destroy = "{{ action('Special\SpecialUploadController@destroy') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('Special.Upload.list')
    </div>

@endsection
