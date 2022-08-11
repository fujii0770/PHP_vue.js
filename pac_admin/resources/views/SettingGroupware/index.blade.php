@extends('../layouts.main')

@push('scripts')
    <script>
        <?php
        $server_domain= config('app.gw_domain');
        ?>
        var appPacAdmin = initAngularApp(),
        link_ajax = "/api/v1/admin/mst-facility",
        link_ajax_domain = "https://{{$server_domain}}",
        link_ajax_delete = "{{ url('setting-groupware/facility') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>

    <div ng-controller="IndexController">
        @include('SettingGroupware.list_facility')
        @include('SettingGroupware.detail_facility')
    </div>

@endsection

@push('scripts')
    <script>
    if(appPacAdmin){
        appPacAdmin.controller('IndexController', function($scope, $rootScope, $http) {
            var hasChangeFacility = false;
        });
    }
    </script>
@endpush

