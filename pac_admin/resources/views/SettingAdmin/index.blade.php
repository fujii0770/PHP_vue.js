@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Admin\SettingAdminController@index') }}";
        var link_reset = "{{ route('settingadmin.resetpass') }}";
        var link_permission = "{{ route('master.permission') }}";
        var link_user_permission = "{{ route('settingadmin.getPermission') }}";
        var link_resetpermission = "{{ route('settingadmin.resetpermission') }}";
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
        var currentUserId = "{{ Auth::user()->id }}";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('SettingAdmin.list')

        @include('SettingAdmin.detail')

        @include('SettingAdmin.permission')
    </div>

@endsection