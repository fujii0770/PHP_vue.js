@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        link_ajax = "{{ route('LongTerm.LongTermIndex.Store') }}";
        link_show = "{{ route('LongTerm.LongTermIndex.Show') }}";
        var currentUserId = "{{ Auth::user()->id }}";
    </script>
@endpush

@section('content')
    @php
        $loggerCompany = \App\Http\Utils\AppUtils::getLoggedCompany(1);
    @endphp

    <span class="clear"></span>
     
    <div ng-controller="IndexController">
        <ul class="nav nav-tabs">
            <li class="nav-item" ng-click="onShowTab('longterm')">
                <a class="nav-link"
                    ng-class="{active: showTab =='longterm' }"
                     data-toggle="tab" href="#longterm">長期保管インデックス</a>
            </li>
            <li class="nav-item" ng-click="onShowTab('longterm_template')"  ng-if="{{ $templateFlg }}">
                <a class="nav-link" 
                    ng-class="{active: showTab =='longterm_template' }"
                    data-toggle="tab" href="#longterm_template">テンプレートインデックス一覧</a>
            </li>
            {{-- PAC_5-2495 --}}
            <li class="nav-item" ng-click="onShowTab('longterm_form_issuance')"  ng-if="{{ $loggerCompany->frm_srv_flg === 1 }}">
                <a class="nav-link"
                   ng-class="{active: showTab =='longterm_form_issuance' }"
                   data-toggle="tab" href="#longterm_form_issuance">明細インデックス一覧</a>
            </li>
            {{-- PAC_5-2495 --}}
        </ul>

        <div class="tab-content">
            <div class="tab-pane" id="longterm"
                ng-class="{active: showTab =='longterm', fade: showTab !='longterm' }">
                @include('LongTerm.longTermIndex.list')
                @include('LongTerm.longTermIndex.detail')
            </div>
                <div class="tab-pane" id="longterm_template"
                    ng-class="{active: showTab =='longterm_template', fade: showTab !='longterm_template' }">
                    @include('LongTerm.longTermIndex.template')
                    @include('LongTerm.longTermIndex.template_detail')

                </div>
            {{-- PAC_5-2495 --}}
            <div class="tab-pane" id="longterm_form_issuance"
                 ng-class="{active: showTab =='longterm_form_issuance', fade: showTab !='longterm_form_issuance' }">
                @include('LongTerm.longTermIndex.formIssuance')
                @include('LongTerm.longTermIndex.formIssuance_detail')
            </div>
            {{-- PAC_5-2495 --}}
            @include('DepartmentTitle.import')
          </div>        
    </div>
   
@endsection
 
@push('scripts')
    <script src="{{ asset('/js/libs/Sortable/Sortable.js') }}"></script>
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('IndexController', function($scope, $rootScope, $http) {
                $scope.showTab = localStorage.getItem('longterm_template.tab');
                if(!$scope.showTab) $scope.showTab = 'longterm';

                $scope.onShowTab = function(tab){
                    $scope.showTab = tab;
                    localStorage.setItem('longterm_template.tab', tab);
                }
            });
        }
    </script>
    <script>   document.oncontextmenu = function () {return false;} </script>
@endpush