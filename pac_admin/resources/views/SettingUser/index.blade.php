@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ action('Admin\UserController@index') }}",
        link_import = "{{ route('user.import') }}",
        link_searchstamp = "{{ route('user.searchstamp') }}",
        link_getDepartmentStamp = "{{ route('user.getDepartmentStamp') }}",
        link_store_stamp = "{{ route('assignstamps.store') }}",
        link_remove_stamp = "{{ route('assignstamps.delete') }}",
        link_show_time_stamp_permission = "{{ route('assignstamps.getTimeStampPermission')}}",
        link_update_time_stamp_permission = "{{ route('assignstamps.updateTimeStampPermission')}}",
        link_reset = "{{ route('user.resetpass') }}",
        link_send_login_url = "{{ route('user.sendLoginUrl') }}",
        link_getDepartmentStampInfo = "{{ route('user.getDepartmentStampInfo') }}",  // 捺印が生成されたときに情報を取得します
        link_deletes = "{{ route('user.deletes') }}",
        link_import_detail = "{{ action('Admin\UserImportCsvController@index') }}";
        link_ajax_csv = "{{ route('CsvUserSetting') }}";
        link_get_stamp_over_status = "{{ url('get-stamp-over-status') }}";
        link_find_user_stamp_ok_status = "{{ url('find-user-stamp-ok-status') }}";
        // PAC_5-2163  利用者情報更新画面でパスワード設定依頼を送るときメールが無効だったらモーダル表示させる
        link_checkUserEmailOrStampOrStatus = "{{route('user.checkUserEmailOrStampOrStatus')}}";
        link_setUsersEmailOrStampOrStatus = "{{route('user.setUsersEmailOrStampOrStatus')}}";
        link_show_password_list = "{{route('user.showPasswordList')}}";
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
    </script>
@endpush

@section('content')


    <span class="clear"></span>
    <div class="SettingUser">
        @include('SettingUser.list')

        @include('SettingUser.detail')

        @include('SettingUser.import')
    </div>

@endsection
