@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ route('AppRole.Index') }}";
        var link_ajax_store = "{{ route('AppRole.store') }}";
        var link_ajax_update ="{{ route('AppRole.update') }}";
        var link_ajax_detail_update ="app-role-detailupdate";
        var link_ajax_detail_store  ="app-role-detailstore";
        var link_ajax_detail_delete  ="app-role-detaildelete";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('SettingGroupware.AppRole.list')

        @include('SettingGroupware.AppRole.detail')
    </div>

@endsection