@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('department-title') }}",
        link_dep_import = "{{ route('Department.import') }}",
        link_pos_import = "{{ route('Position.import') }}";
        link_ajax_position_csv = "{{ route('CsvPosition') }}";
        link_ajax_department_csv = "{{ route('CsvDepartment') }}";
        link_ajax_update_department_sort = "{{action([\App\Http\Controllers\DepartmentTitleController::class,'updateDepartmentSort'])}}"
        link_ajax_update_position_sort = "{{action([\App\Http\Controllers\DepartmentTitleController::class,'updatePositionSort'])}}"
        link_ajax_get_department = "{{action([\App\Http\Controllers\DepartmentTitleController::class,'getDepartment'])}}"
    </script>
@endpush

@section('content')
    
    <span class="clear"></span>
     
    <div ng-controller="IndexController">
        <ul class="nav nav-tabs">
            <li class="nav-item" ng-click="onShowTab('department')">
                <a class="nav-link"
                    ng-class="{active: showTab =='department' }"
                     data-toggle="tab" href="#department">部署</a>
            </li>
            <li class="nav-item" ng-click="onShowTab('position')">
                <a class="nav-link" 
                    ng-class="{active: showTab =='position' }"
                    data-toggle="tab" href="#position">役職</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane" id="department"
                ng-class="{active: showTab =='department', fade: showTab !='department' }">
                @include('DepartmentTitle.list_department')
                @include('DepartmentTitle.detail_department')
            </div>
            <div class="tab-pane" id="position"
                ng-class="{active: showTab =='position', fade: showTab !='position' }">
                @include('DepartmentTitle.list_position')
                @include('DepartmentTitle.detail_position')
            </div>
            @include('DepartmentTitle.import')
          </div>        
    </div>
   
@endsection
 
@push('scripts')
    <script src="{{ asset('/js/libs/Sortable/Sortable.js') }}"></script>
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('IndexController', function($scope, $rootScope, $http) {
                $scope.showTab = localStorage.getItem('departmettitle.tab');
                if(!$scope.showTab) $scope.showTab = 'department';

                $scope.onShowTab = function(tab){
                    $scope.showTab = tab;
                    localStorage.setItem('departmettitle.tab', tab);
                }
            });
        }
    </script>
    <script>   document.oncontextmenu = function () {return false;} </script>
@endpush