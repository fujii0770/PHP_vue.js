@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ action('Admin\AuditAccountController@index') }}",
        link_delete_select ="{{route('AuditDelete')}}",
        link_reset = "{{ route('audit.resetpass') }}";
        link_send_login_url = "{{ route('audit.sendLoginUrl') }}";
        var hasChange = false, allow_create = 1, allow_update = 1;
    </script>
@endpush

@section('content')


    <span class="clear"></span>
    <div class="SettingAuditAccount">
        @include('SettingAuditAccount.list')

        @include('SettingAuditAccount.detail')
    </div>

@endsection
