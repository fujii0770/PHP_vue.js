@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Admin\ReceiveUserController@index') }}";
        var link_reset = "{{ route('receive_user.resetPass') }}";
        var link_send_login_url = "{{ route('receive_user.sendLoginUrl') }}";
        var link_deletes = "{{ route('receive_user.deletes') }}";
        var link_get_stamp_over_status = "{{ url('get-stamp-over-status') }}";
        var link_search_stamp = "{{ route('receive_user.search_stamps') }}";
        var link_store_stamp = "{{ route('receive_user.store_stamp') }}";
        var link_remove_stamp = "{{ route('assignstamps.delete') }}";
        var hasChange = false;
        var allow_create = {{ $allow_create?1:0 }};
        var allow_update = {{ $allow_update?1:0 }};
        // PAC_5-2163  利用者情報更新画面でパスワード設定依頼を送るときメールが無効だったらモーダル表示させる
        link_checkUserEmailOrStampOrStatus = "{{route('user.checkUserEmailOrStampOrStatus')}}";
        link_setUsersEmailOrStampOrStatus = "{{route('user.setUsersEmailOrStampOrStatus')}}";
        link_find_user_stamp_ok_status = "{{ url('find-user-stamp-ok-status') }}";
        link_show_time_stamp_permission = "{{ route('assignstamps.getTimeStampPermission')}}";
        link_update_time_stamp_permission = "{{ route('assignstamps.updateTimeStampPermission')}}";
        link_import = "{{ route('receive_user.import') }}";//CSV取込
        link_ajax_csv = "{{ route('receiver_user.download_csv') }}";//CSV出力予約
        link_show_password_list = "{{route('receiver_user.showPasswordList')}}";
    </script>
@endpush

@section('content')
    <span class="clear"></span>
    <div class="SettingReceiveUser">
        @include('SettingReceiveUser.list')
        @include('SettingReceiveUser.detail')

        @include('SettingReceiveUser.import')
    </div>

@endsection
