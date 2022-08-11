@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('Admin\TemplateRouteController@store') }}";
        var link_deletes = "{{ route('templateRoute.deletes') }}";
        var link_import = "{{ route('templateRoute.import') }}"; // PAC_5-2133 CSV取込
        var link_export = "{{ route('CsvTemplateRoute') }}";

        var hasChange = false, allow_create = {{ $allow_create?1:0 }}, allow_update = {{ $allow_update?1:0 }};
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('TemplateRoute.list')

        @include('TemplateRoute.detail')
        {{--PAC_5-2133 CSV取込--}}
        @include('TemplateRoute.import')
    </div>

@endsection