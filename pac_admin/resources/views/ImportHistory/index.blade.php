@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ action('Admin\UserImportCsvController@index') }}";
    </script>
@endpush

@section('content')


    <span class="clear"></span>
    <div class="ImportHistory">
        @include('ImportHistory.import_list')

        @include('ImportHistory.import_detail')
    </div>

@endsection
