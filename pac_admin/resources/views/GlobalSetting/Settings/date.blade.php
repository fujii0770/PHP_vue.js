@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            link_ajax = "{{ route('GlobalSetting.Date.Store') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div class="GlobalSetting Settings-date">
        <div ng-controller="SettingsDateController">
            <div class="message"></div>
            <div class="card mt-3">
                <div class="card-header">日付印設定</div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-lg-2"></div>
                        <label for="" class="control-label col-3 text-right">日付形式</label>
                        <div class="col-9 col-lg-3">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::DATE_STAMP_FORMAT, 'dstamp_style', $company->dstamp_style,null,
                            ['class'=> 'form-control', 'ng-model' =>'dstamp_style', 'ng-readonly'=>"readonly",'ng-change' => 'changeFormat()']) !!}
                        </div>
                        <div class="col-lg-4"></div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-2"></div>
                        <label for="" class="col-3"></label>
                        <div class="col-9 col-lg-3"><% example_date %></div>
                        <div class="col-lg-4"></div>
                    </div>
                </div>
            </div>
            @canany([PermissionUtils::PERMISSION_DATE_STAMP_SETTING_UPDATE])
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-success" ng-click="save()">
                    <i class="far fa-save"></i> 更新
                </button>
            </div>
            @endcanany
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('SettingsDateController', function($scope, $rootScope, $http){

                $scope.dstamp_style = "{!!  $company->dstamp_style  !!}";
                $scope.example_date = "";
                $scope.date = {sperator: '.', character: '’'};

                $scope.changeFormat = function(){
                    $scope.showDateFormat();
                }

                $scope.showDateFormat = function(){
                    let m = '02', year = 2001, d = '03', sperator = '.', y = '’01';

                    switch($scope.dstamp_style){
                        case '\'y.m.d':
                            sperator = ".";
                            y = '’01';
                            break;
                        case '\'y/m/d':
                            sperator = "/";
                            y = '’01';
                            break;
                        case 'Y.m.d':
                            sperator = ".";
                            y = 2001;
                            break;
                        case 'Y/m/d':
                            sperator = "/";
                            y = 2001;
                            break;
                        case 'gy.m.d':
                            sperator = ".";
                            y = 'H13';
                            break;
                        case 'gy/m/d':
                            sperator = "/";
                            y = 'H13';
                            break;
                    }

                    $scope.example_date = '例) '+year+'年'+m+'月'+d+'日→'+y+''+sperator+''+m+''+sperator+''+d;
                };

                $scope.showDateFormat();

                $scope.save = function(){
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax, {dstamp_style: $scope.dstamp_style})
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $(".message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $(".message").append(showMessages(event.data.message, 'success', 10000));
                        }
                    });
                };
            });

        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush
