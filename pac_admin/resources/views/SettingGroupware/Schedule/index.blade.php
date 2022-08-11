@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
            update_schedule_ajax = "{{ url('setting-groupware/update-schedule') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div class="GlobalSetting Settings-limit">
        <div ng-controller="SettingsScheduleController">
            <div class="message"></div>
            <div class="card mt-3">
                <div class="card-header">制限設定</div>
                <div class="card-body form-horizontal">
                    <div class="form-group row">
                        <label for="enable_schedule_1" class="control-label col-md-4">スケジュールの重複予約</label>
                        <div class="col-md-8">
                            <label class="label mr-2" for="enable_schedule_1">
                                <input type="radio" ng-model="repeatFlg" id="enable_schedule_1" ng-checked="repeatFlg==1" ng-value="1"> 可能
                            </label>
                            <label class="label mr-2" for="enable_schedule_0">
                                <input type="radio" ng-model="repeatFlg" id="enable_schedule_0" ng-checked="repeatFlg==0" ng-value="0"> 不可
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_VIEW])
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
        if ("{{$failure_message}}" != "") {
            $(".message").append(showMessages(["{{$failure_message}}"], 'danger', 10000));
        }
        appPacAdmin.controller('SettingsScheduleController', function($scope, $rootScope, $http){
            $scope.repeatFlg="{{$responseBody}}";
            $scope.save=function(){
                $rootScope.$emit("showLoading");
                $http.post(update_schedule_ajax,{repeatFlg:$scope.repeatFlg})
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $(".message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $(".message").append(showMessages(event.data.message, 'success', 10000));
                            location.reload();
                        }
                    });
            }
        })
    }
</script>
@endpush
