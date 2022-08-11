@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ action('Admin\CommonAddressController@index') }}",
        link_import = "{{ route('AddressImport') }}",
        link_delete_select ="{{route('AddressDelete')}}";
        link_ajax_address_common ="{{route('CsvAddressCommon')}}";
        
        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
    </script>
@endpush

@section('content')
   
    <span class="clear"></span>
    <div >
        @include('SettingAddress.list')

        @include('SettingAddress.detail')

        @include('SettingAddress.import')
    </div>

@endsection