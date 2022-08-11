@php
    $loggerCompany = \App\Http\Utils\AppUtils::getLoggedCompany(1);
@endphp
<div ng-controller="PermissionSettingAdminController">
    <form class="form_edit" action="" method="" onsubmit="return false;">
        <div class="modal modal-detail" id="modalGrantPermission" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">管理者権限設定</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message"></div>
                    <div class="permission">
                        <table class="tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                            <thead>
                                <tr>
                                    <th class="title" scope="col" style="min-width: 30%">機能名</th>
                                    <th scope="col">参照権限</th>
                                    <th scope="col">生成権限</th>
                                    <th scope="col">編集権限</th>
                                    <th scope="col">削除権限</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat-start="groupName in permissionGroup" class="row-group">
                                    <td colspan="5"><% groupName %></td>
                                </tr>
                                <tr ng-repeat-end ng-repeat="(menu, actions) in allPermission[groupName]" class="row-menu">
                                    <td><% menu %></td>
                                    <td class="text-center">
                                        <input ng-if="actions['view']" type="checkbox" ng-checked="detailPermisson.indexOf(actions['view']) !== -1" ng-click="changePermisson(actions['view'],1)" />
                                    </td>
                                    <td class="text-center"><input ng-if="actions['create']" type="checkbox" ng-checked="detailPermisson.indexOf(actions['create']) !== -1 " ng-click="changePermisson(actions['create'],2)" ng-disabled="detailPermisson.indexOf(actions['view']) === -1 " /></td>
                                    <td class="text-center"><input ng-if="actions['update']" type="checkbox" ng-checked="detailPermisson.indexOf(actions['update']) !== -1 " ng-click="changePermisson(actions['update'],3)" ng-disabled="detailPermisson.indexOf(actions['view']) === -1 " /></td>
                                    <td class="text-center"><input ng-if="actions['delete']" type="checkbox" ng-checked="detailPermisson.indexOf(actions['delete']) !== -1 " ng-click="changePermisson(actions['delete'],4)" ng-disabled="detailPermisson.indexOf(actions['view']) === -1 " /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="message mt-3"></div>
                </div>

                <div class="modal-footer column">
                    <div class="row">
                        <div class="col-lg-8 col-btn-left">
                                <button type="button" class="btn btn-default" ng-click="selectAll()" ><i class="far fa-check-square"></i> 全選択</button>
                                <button type="button" class="btn btn-danger" ng-click="deselectAll()"><i class="far fa-window-close"></i> 全選択解除</button>
                                <button type="button" class="btn btn-warning" ng-click="resetDefault()"><i class="fas fa-undo"></i> 初期設定に戻す</button>
                        </div>
                        <div class="col-lg-4 col-btn-right">
                            <button type="button" class="btn btn-success" ng-click="save()"><i class="far fa-save"></i> 更新</button>
                            <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fas fa-times-circle"></i> 閉じる</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        appPacAdmin.controller('PermissionSettingAdminController', function($scope, $rootScope, $http) {
            $rootScope.permissionGroupTmp = {!! json_encode(App\Http\Utils\PermissionUtils::GROUP_MENU) !!};
            $rootScope.permissionGroup = $rootScope.permissionGroupTmp.filter(function(groupName){
                if(groupName == 'HR機能' && <?= $loggerCompany['hr_flg'] ?> == 0){
                    return false
                    }
                if(groupName == 'グループウェア設定' && ((<?= $loggerCompany['gw_flg'] ?> == 0 && <?= $loggerCompany['board_flg'] ?> == 0) || <?= $loggerCompany['portal_flg'] ?> == 0)){
                    return false
                    }
                if(groupName == 'スケジューラ設定' && (<?= $loggerCompany['scheduler_flg'] ?> == 0 || <?= $loggerCompany['portal_flg'] ?> == 0)){
                    return false
                    }
                if(groupName == 'ササッと明細' && <?= $loggerCompany['frm_srv_flg'] ?> == 0){
                    return false
                }
                if (groupName == '派遣管理' && <?= $loggerCompany['dispatch_flg'] ?> == 0){
                    return false
                }
                if (groupName == '特設サイト' && <?= $loggerCompany['special_receive_flg'] ?> == 0 && <?= $loggerCompany['special_send_flg'] ?> == 0){
                    return false
                }
                if (groupName == 'ササッとTalk設定' && <?= $loggerCompany['chat_flg'] ?> == 0){
                    return false
                }
                if (groupName == '経費精算' && <?= $loggerCompany['expense_flg'] ?> == 0){
                    return false
                }
                if (groupName == '長期保管' && <?= $loggerCompany['long_term_storage_flg'] ?> == 0){
                    return false
                }
                return true
            })

            $rootScope.changePermisson = function(id,opration){
                var index = $rootScope.detailPermisson.indexOf(id);
                if(index !== -1){
                    $rootScope.detailPermisson.splice(index, 1);

                    if (opration === 1) {
                        for(let i in $rootScope.permissionGroup){
                            let groupName = $rootScope.permissionGroup[i];
                            for(let j in $rootScope.allPermission[groupName]){
                                let actions = $rootScope.allPermission[groupName][j];
                                // console.info(actions.view);
                                if (actions.view == id){
                                    if(actions.create !== undefined){
                                        // console.info(actions.create);
                                        var index2 = $rootScope.detailPermisson.indexOf(actions.create);
                                        if(index2 !== -1){
                                            $rootScope.detailPermisson.splice(index2, 1);
                                        }
                                    }

                                    if(actions.update !== undefined){
                                        // console.info(actions.create);
                                        var index3 = $rootScope.detailPermisson.indexOf(actions.update);
                                        if(index3 !== -1){
                                            $rootScope.detailPermisson.splice(index3, 1);
                                        }
                                    }

                                    if(actions.delete !== undefined){
                                        // console.info(actions.create);
                                        var index4 = $rootScope.detailPermisson.indexOf(actions.delete);
                                        if(index4 !== -1){
                                            $rootScope.detailPermisson.splice(index4, 1);
                                        }
                                    }

                                }
                            }
                        }
                    }

                }else{
                    $rootScope.detailPermisson.push(id);
                }
           };

           $rootScope.selectAll = function(){
                $rootScope.detailPermisson = [];
                for(let i in $rootScope.permissionGroup){
                    let groupName = $rootScope.permissionGroup[i];
                    for(let j in $rootScope.allPermission[groupName]){
                        let actions = $rootScope.allPermission[groupName][j];
                        if(actions.view !== undefined)  $rootScope.detailPermisson.push(actions.view);
                        if(actions.create !== undefined)  $rootScope.detailPermisson.push(actions.create);
                        if(actions.update !== undefined)  $rootScope.detailPermisson.push(actions.update);
                        if(actions.delete !== undefined)  $rootScope.detailPermisson.push(actions.delete);
                    }
                }
            }


           $rootScope.deselectAll = function(){
                $rootScope.detailPermisson = [];
            }

           $rootScope.resetDefault = function(){
            $rootScope.$emit("showLoading");
                $http.get(link_resetpermission +"/"+ $rootScope.id)
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalGrantPermission .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $rootScope.detailPermisson = event.data.items;
                        }
                        $("#modalGrantPermission").modal();
                    });
            }

            $rootScope.save = function(){
                hideMessages();
                $rootScope.$emit("showLoading");
                $http.post(link_user_permission +"/" + $rootScope.id, { items: $rootScope.detailPermisson,email: $rootScope.detailPermissonEmail })
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $("#modalGrantPermission .message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $rootScope.info.id = event.data.id;
                            $("#modalGrantPermission .message").append(showMessages(event.data.message, 'success', 10000));
                            hasChange = true;
                        }
                    });
            }
        });
    </script>
@endpush

@push('styles_after')
    <style>
        .row-group{ background: #fcd5b5; }
        .row-group td{     color: #000; font-weight: bold; }
        .row-menu{ }
        .row-menu td{ }
    </style>
@endpush
