@extends('../layouts.main')

@push('scripts')
    <script>
        <?php
        $server_domain = config('app.gw_domain');
        ?>
        var appPacAdmin = initAngularApp(),
            link_ajax_index = "{{route('colorCategory.index')}}",
            link_ajax_show = "{{route('colorCategory.show')}}",
            link_ajax_create = "{{route('colorCategory.create')}}",
            link_ajax_update = "{{route('colorCategory.update')}}",
            link_ajax_delete = "{{route('colorCategory.delete')}}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>

    <div ng-controller="IndexController">
        @include('SettingGWColorCategory.list')
        @include('SettingGWColorCategory.detail')
    </div>

@endsection

@push('scripts')
    <script>
        if (appPacAdmin) {
            appPacAdmin.controller('IndexController', function ($scope, $rootScope, $http) {
                var hasChangeFacility = false;
            });
        }
    </script>
@endpush

