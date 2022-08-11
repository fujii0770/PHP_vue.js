<div ng-controller="ListSettingAdminController">

    <form action="" name="adminForm">
        <div class="card mt-3">
            <div class="card-header">電子証明書設定</div>
            <div class="card-body">
                @canany([PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_CREATE])
                    <div class="text-right">
                        <button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-plus-circle"></span> 登録</button>
                        <input id="import_file" accept=".pfx,.p12" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" >
                    </div>
                @endcanany

                <div class="message"></div>

                <span class="clear"></span>
                <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>
                            <th class="title" scope="col">
                                名称
                            </th>
                            <th scope="col" >
                                利用証明書
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                            <tr>
                                <td class="title">
                                    <input type="text" class = "custom-input custom-width" ng-disabled= "{{ !$company->certificate_name}}" ng-click="editRecord(0)"  value="Shachihata.inc">
                                </td>
                                <td>
                                    <i ng-if="{{ !$company->certificate_flg}}" class="fas fa-check"></i>
                                    <input type="text" class = "custom-input" ng-disabled= "{{ !$company->certificate_name}}" ng-click="editRecord(0)">
                                </td>
                            </tr>
                            @if ($company->certificate_name)
                                <tr>
                                    <td class="title" ng-click="editRecord({{ $company->id }})">{{ $company->certificate_name}}</td>
                                    <td ng-click="editRecord({{ $company->id }})" ><i ng-if="{{ $company->certificate_flg}}" class="fas fa-check"></i></td>
                                </tr>
                            @endif
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <form class="form_edit_stamp" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail-stamp" id="modalCertificateSetting" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">電子証明書アップロード</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body form-horizontal">
                        <div class="mt-3">
                            <div class="client-message message message-info"></div>
                            <div>アップロードファイル</div>
                            <table class="tablesaw-list table-sort-client tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                                <thead>
                                <tr>
                                    <th scope="col">アップロードファイル名</th>
                                    <th scope="col">パスワード</th>
                                    <th scope="col" style="width: 70px">削除</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="row- row-edit"
                                    ng-repeat="(indexFile, file) in filesSelected" >
                                    <td><% file.name %></td>
                                    <td><input type="password" id="password" class="form-control" maxlength="256" ></td>
                                    <td><div class="btn btn-default btn-sm" ng-click="removeFileSelected(indexFile)">削除</div></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @canany([PermissionUtils::PERMISSION_CERTIFICATE_SETTING_SETTING_UPDATE])
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" ng-click="uploadCertificate()">
                            <i class="far fa-save"></i> アップロード
                        </button>
                    </div>
                    @endcanany
                </div>
            </div>
        </div>
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListSettingAdminController', function($scope, $rootScope, $http){
                $rootScope.info = {};
                $scope.company = {!! json_encode($company) !!};

                $scope.filesSelected = [];

                $scope.SelectFile = function($event){
                    if($event.target.files.length){
                        $scope.filesSelected.length = 0;
                        $scope.$applyAsync ( function(){
                            $scope.filesSelected.push($event.target.files[0]);
                        });

                        $("#modalCertificateSetting").modal();
                    }
                };

                $scope.removeFileSelected = function(index){
                    $('#import_file').val('');
                    $("#modalCertificateSetting").modal('hide');
                    $('#import_file').trigger('click');
                };

                $scope.uploadCertificate = function(){
                    if ($('#password').val()){
                        $rootScope.$emit("showLoading");
                        var fd = new FormData();
                        fd.append('file', $scope.filesSelected[0]);
                        fd.append('password', $('#password').val());
                        fd.append('create',true);
                        $('#import_file').val("");
                        $http.post(link_update, fd, { headers: { 'Content-Type': undefined }})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                console.log(JSON.stringify(event.data));
                                if(event.data.status == false){
                                    $(".client-message").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".client-message").append(showMessages(event.data.message, 'success', 10000));
                                    location.reload();
                                }
                            })
                    }else{
                        $(".client-message").append(showMessages(['パスワードを入力してください'], 'danger', 10000));
                    }
                };

                $scope.editRecord = function(id){
                    $rootScope.id = id;
                    $rootScope.info.id = id;
                    $rootScope.checkCompany = true;
                    hideMessages();
                    hasChange = false;
                    if(allow_update) $rootScope.readonly = false;
                    else $rootScope.readonly = true;
                    $rootScope.$emit("showLoading");
                    $http.get(link_detail +"/"+$scope.company.id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalDetailItem .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $rootScope.info = event.data.info;
                            if($rootScope.id == 0){
                                $rootScope.info.certificate_name = "Shachihata.inc";
                                $rootScope.info.certificate_flg = Number(!$scope.company.certificate_flg);
                                $rootScope.checkCompany = false;
                            }
                        }
                    });
                    $("#modalDetailItem").modal();
                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
             if(hasChange){
                 location.reload();
             }
        });
    </script>
@endpush
