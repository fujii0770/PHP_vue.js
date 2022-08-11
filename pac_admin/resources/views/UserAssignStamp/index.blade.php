@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ action('Admin\UserController@index') }}",
        link_import = "{{ route('user.import') }}",
        link_searchstamp = "{{ route('user.searchstamp') }}",
        link_store_stamp = "{{ route('assignstamps.store') }}";
        link_remove_stamp = "{{ route('assignstamps.delete') }}",
        link_show_time_stamp_permission = "{{ route('assignstamps.getTimeStampPermission')}}",
        link_update_time_stamp_permission = "{{ route('assignstamps.updateTimeStampPermission')}}",
        link_search_CompanyStamp = "{{ route('GlobalSetting.CompanyStamp.Search') }}";
        var link_reset = "{{ route('user.resetpass') }}";
        var link_ajax_search_convenient_stamp = "{{ route('GlobalSetting.CompanyConvenientStamp.Search') }}";
        var link_check_store_convenient_stamp = "{{ route('assignstamps.checkStoreConvenientStamp') }}";

        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
    </script>
@endpush

@section('content')


    <span class="clear"></span>
    <div class="SettingUser">
        @include('UserAssignStamp.list')

        @include('UserAssignStamp.detail')
    </div>

@endsection
