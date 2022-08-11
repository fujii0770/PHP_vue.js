@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Admin\OptionUserController@index') }}";
        var link_reset = "{{ route('user.resetpass') }}";
        var link_send_login_url = "{{ route('user.sendLoginUrl') }}";
        var link_deletes = "{{ route('option_user.deletes') }}";
        var hasChange = false;
        var allow_create = {{ $allow_create?1:0 }};
        var allow_update = {{ $allow_update?1:0 }};
        // PAC_5-2163  利用者情報更新画面でパスワード設定依頼を送るときメールが無効だったらモーダル表示させる
        link_checkUserEmailOrStampOrStatus = "{{route('user.checkUserEmailOrStampOrStatus')}}";
        link_setUsersEmailOrStampOrStatus = "{{route('user.setUsersEmailOrStampOrStatus')}}";
        link_import = "{{ route('option_user.import') }}";//CSV取込
        link_ajax_csv = "{{ route('option_user.download_csv') }}";//CSV出力予約
        link_show_password_list = "{{ route('option_user.showPasswordList') }}";
    </script>
@endpush

@section('content')
    <span class="clear"></span>
    <div class="SettingOptionUser">
        @include('SettingOptionUser.list')
        @include('SettingOptionUser.detail')

        @include('SettingOptionUser.import')
    </div>

@endsection
