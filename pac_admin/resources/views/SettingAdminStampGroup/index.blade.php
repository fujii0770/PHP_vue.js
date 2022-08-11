@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Admin\SettingAdminStampGroupController@index') }}";
        var link_updates = "{{ route('adminStampGroup.updates') }}";
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
    <div>
        @include('SettingAdminStampGroup.list')
    </div>

@endsection