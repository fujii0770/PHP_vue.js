@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('WorkDetailController@index') }}";
        var link_update_select ="{{route('WorkDetail.BulkApproval')}}";
        var link_export = "{{ url('roster-list/export') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @include('WorkDetail.list')

        @include('WorkDetail.detail')
    </div>

@endsection
