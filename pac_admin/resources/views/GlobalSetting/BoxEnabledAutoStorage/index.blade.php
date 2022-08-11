@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_list_box = "{{ route('SelectFolersExternal') }}";
        var link_get_box_folder_list = "{{ route('GetCloudItems') }}";
        var link_save_auto_storage_setting = "{{ route('SaveAutoStorageSetting') }}";
        var link_create_folder = "{{ route('CreateFolder') }}";
        var link_re_save_auto_storage = "{{ route('ReSaveAutoStorage') }}";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('GlobalSetting.BoxEnabledAutoStorage.list')
    </div>

@endsection