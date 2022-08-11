@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('GlobalSetting\CompanyStampController@index') }}";
        var link_search_CompanyStamp = "{{ route('GlobalSetting.CompanyStamp.Search') }}";
        var link_ajax_search_convenient_stamp = "{{ route('GlobalSetting.CompanyConvenientStamp.Search') }}";
        var link_download_CompanyStamp = "{{ route('GlobalSetting.CompanyStamp.download') }}";
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
        var currentUserId = "{{ Auth::user()->id }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('GlobalSetting.CompanyStamp.list')

        @include('GlobalSetting.CompanyStamp.detail')
    </div>

@endsection
