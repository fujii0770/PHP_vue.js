@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Shachihata\CompaniesController@index') }}";
        var link_ajax_admin = "{{ action('Admin\SettingAdminController@index') }}";
        var link_ajax_indexadmin = "{{ route('Companies.admin') }}";
        var link_ajax_stamp = "{{ route('Companies.indexStamp') }}";
        var link_reset_password = "{{ route('Companies.resetpass') }}";
        var link_list_company = "{{ route('Companies.getListCompany') }}";
        var link_depStamp_import = "{{ route('Companies.depstamps') }}";

    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('Companies.list')
        @include('Companies.detail')
        @include('Companies.import')
    </div>

@endsection