<div ng-controller="LoadingController">
    <div class="loading <% status %>">
        <i class="icon fas fa-spinner fa-spin"></i>
    </div>
</div>

@push('styles_after')
    <style>
        .loading{ 
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: rgba(0,0,0,0.25);
            z-index: 9999;
            text-align: center;
            display: none;
         }
         .loading.show{ display: block; }

         .loading .icon{
            color: #0984e3; font-size: 100px; position: absolute; left: 50%; top: 50%; margin-left: -50px; margin-top: -50px;
         }
    </style>
@endpush

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        if(appPacAdmin){
            appPacAdmin.controller('LoadingController', function($scope, $rootScope, $timeout){
                $scope.status = 'hide';
                $scope.ttl = 0; // max time to live loading. Auto hide if expirce time
                $rootScope.$on("showLoading", function(event, data){
                    data = data || {};
                    $scope.status = 'show';
                    $scope.ttl = data.ttl || 30000;
                    if($scope.ttl){
                        $timeout(function() {
                            $scope.status = 'hide';
                        }, $scope.ttl);
                    }
                });
                $rootScope.$on("hideLoading", function(event, data){
                    $scope.status = 'hide';
                });
            });
        }
    </script>
@endpush