@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Setting\SettingSanitizingController@index') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('SettingSanitizing.list')
        @include('SettingSanitizing.detail')
    </div>

@endsection
