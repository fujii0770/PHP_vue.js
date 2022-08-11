@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('EditionController@index') }}";


    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('Edition.list')
        @include('Edition.detail')
    </div>

@endsection