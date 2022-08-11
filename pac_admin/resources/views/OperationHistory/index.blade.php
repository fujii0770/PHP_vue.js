@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('OperationHistoryController@index',['type' => $type]) }}";
        var link_ajax_request = "{{ route('CsvHistory') }}";
    </script>
    <script src="{{ asset('/js/monthpicker.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/css/monthpicker.css') }}">

@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('OperationHistory.list')

        @include('OperationHistory.detail')
    </div>

@endsection
