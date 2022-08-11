@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_ajax = "{{ url('long-term/long-term-folder') }}",
            link_ajax_get_parent_permissions = "{{url('long-term/long-term-folder/getParentPermissions')}}"
            link_ajax_save_folder_permissions = "{{url('long-term/long-term-folder/saveFolderPermissions')}}"
            link_ajax_add_users_to_folder_permissions = "{{url('long-term/long-term-folder/addUsersToFolderPermissions')}}"
            link_ajax_delete_users_from_folder_permissions = "{{url('long-term/long-term-folder/deleteUsersFromFolderPermissions')}}"
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div class="SettingUser">
        @include('LongTerm.longTermFolder.list')
        @include('LongTerm.longTermFolder.detail_folder')
        @include('LongTerm.longTermFolder.detail_folder_permissions')
    </div>

@endsection
