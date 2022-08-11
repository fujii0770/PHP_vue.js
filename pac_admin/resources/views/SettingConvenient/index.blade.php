@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Setting\\SettingConvenientController@index') }}";
        var link_ajax_search_stamp = "{{ route('StampConvenient.Search') }}";
        var link_ajax_upload_stamp = "{{ route('StampConvenient.Upload') }}";
        var currentUserId = "{{ Auth::user()->id }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('SettingConvenient.list')
        @include('SettingConvenient.detail')
    </div>

@endsection
