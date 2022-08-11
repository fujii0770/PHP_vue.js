@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        var link_ajax = "{{ action('GlobalSetting\IpRestrictionController@index') }}";
        var link_refresh = link_ajax + '?refresh=true';
        var link_delete_select ="{{route('IpRestrictionDelete')}}";
        var hasChange = false;
    </script>
@endpush

@push('styles_before')
    <style>
        th.tablesaw-sortable-ascending .tablesaw-sortable-arrow::after { content: ''; !important }
        th.tablesaw-sortable-descending .tablesaw-sortable-arrow::after { content: ''; !important }
        
        th:not(.tablesaw-sortable-descending):not(.tablesaw-sortable-ascending) .fa-sort { position: absolute; right: 1em; color: #afafaf; }
        th.tablesaw-sortable-descending .fa-sort { position: absolute; right: 1em; display: none; }
        th.tablesaw-sortable-ascending .fa-sort { position: absolute; right: 1em; display: none; }

        th:not(.tablesaw-sortable-descending) .fa-caret-down { position: absolute; right: 1em; display: none; }
        th.tablesaw-sortable-descending .fa-caret-down { position: absolute; right: 1em; }

        th:not(.tablesaw-sortable-ascending) .fa-caret-up { position: absolute; right: 1em; display: none; }
        th.tablesaw-sortable-ascending .fa-caret-up { position: absolute; right: 1em; }
    </style>
@endpush

@section('content')

    <span class="clear"></span>
    <div>
        @if($disabled)
            <p>この機能は現在無効化されています。</p>
        @else
        @include('GlobalSetting.IpRestriction.list')

        @include('GlobalSetting.IpRestriction.detail')
        @endif
    </div>

@endsection
