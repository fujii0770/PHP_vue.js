@extends('../layouts.main')

@push('scripts')
    <?php
    $server_domain= config('app.gw_domain');
    ?>
    <script>  
        var appPacAdmin = initAngularApp(),
        link_ajax = "/api/v1/admin/sync",
        link_ajax_domain = "https://{{$server_domain}}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>

    <div class="Settings-master-sync">
        <div ng-controller="SettingsMasterSyncController">
            <div class="message"></div>
            <div class="card mt-3">
                <div class="card-header">マスタ同期設定</div>
                <div class="card-body form-horizontal">
                    <div class="form-group">下記で選択したシステムに対して、Shachihata cloud上で登録していただいたマスタデータを反映いたします。</div>
                    <div class="col-lg-4 form-group">
                        <div class="row">
                            <label class="col-md-4 control-label" >システム名</label>
                            <div class="col-md-8">
                                <select name="syncmstname" class="form-control" id="syncmstname">
                                    {{--<option value="0">なし</option>--}}
                                    <option value="1">グループウェア</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">※管理者設定、利用者設定、部署・役職画面で登録いただいた情報を反映いたします。</div>
                </div>
            </div>
            @canany([PermissionUtils::PERMISSION_MASTER_SYNC_SETTING_UPDATE])
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
        function htmlspecialchars_decode(ch) { 
            ch = ch.replace(/\&amp\;/g,"&") ;
            ch = ch.replace(/\&quot\;/g,'"') ;
            ch = ch.replace(/\&#039\;/g,"'") ;
            ch = ch.replace(/\&lt\;/g,"<") ;
            ch = ch.replace(/\&gt\;/g,">") ;
            return ch ;
        }

        if(appPacAdmin){
            if ("{{$failure_message}}" != "") {
                $(".message").append(showMessages(["{!! $failure_message !!}"], 'danger', 10000));
            }
            appPacAdmin.controller('SettingsMasterSyncController', function($scope, $rootScope, $http){

                $scope.save = function(){
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'更新しますか？',
                        btnSuccess:'はい',
                        databack: $scope.selectedID,
                        callSuccess: function(selectedID){
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax_domain + link_ajax, htmlspecialchars_decode("{{$syncdata}}"))
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.status != 200){
                                        $(".message").append(showMessages(['更新に失敗しました。'], 'danger', 10000));
                                    }else{
                                        $(".message").append(showMessages(['更新に成功しました。'], 'success', 10000));
                                    }
                                }).catch(function(e){
                                    $rootScope.$emit("hideLoading");
                                    $(".message").append(showMessages(['大変申し訳ございません。アクセス集中の為、データの取得、または更新に失敗しました。</br> お手数をおかけしますが、時間を置いてから再度お試しください。'], 'danger', 10000));
                            });
                        }
                    });
                };
            });
        }


    </script>
@endpush

@push('styles_after')
    <style>
        .help-message{
            color: #8a6d3b;
            background-color: #fcf8e3;
            border: solid 1px #faebcc;
            padding: 5px;
         }
    </style>
@endpush
