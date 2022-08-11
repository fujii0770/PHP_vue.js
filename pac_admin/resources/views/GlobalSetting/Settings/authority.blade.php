@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('GlobalSetting.Authority.Store') }}";
    </script>
@endpush

@section('content')

@php
    $loggerCompany = \App\Http\Utils\AppUtils::getLoggedCompany(1);
@endphp

    <span class="clear"></span>

        <div class="GlobalSetting Settings-authority mb-3">
            <div ng-controller="SettingsAuthorityController">
                <div class="message"></div>
                <div class="card mt-3">
                    <div class="card-header">管理者権限初期値設定</div>
                    <div class="card-body">
                        <table class="tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                            <thead>
                                <tr>
                                    <th scope="col" style="min-width: 30%;">機能名</th>
                                    <th scope="col" style="text-align: center;">参照権限</th>
                                    <th scope="col" style="text-align: center;">生成権限</th>
                                    <th scope="col" style="text-align: center;">編集権限</th>
                                    <th scope="col" style="text-align: center;">削除権限</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(App\Http\Utils\PermissionUtils::GROUP_MENU as $groupName)
                                    @if($groupName == 'グループウェア設定' && (!$loggerCompany->portal_flg || (!$loggerCompany->gw_flg && !$loggerCompany->board_flg)))
                                        @continue
                                    @endif
                                    @if($groupName == 'スケジューラ設定' && (!$loggerCompany->portal_flg || !$loggerCompany->scheduler_flg))
                                        @continue
                                    @endif
                                    @if($groupName == 'HR機能' && !$loggerCompany->hr_flg)
                                        @continue
                                    @endif
                                    @if($groupName == 'ササッと明細' && !$loggerCompany->frm_srv_flg)
                                        @continue
                                    @endif
                                    @if($groupName == '派遣管理' && !$loggerCompany->dispatch_flg)
                                        @continue
                                    @endif
                                    @if($groupName == '特設サイト' && !$loggerCompany->special_receive_flg && !$loggerCompany->special_send_flg)
                                        @continue
                                    @endif
                                    @if($groupName == 'ササッとTalk設定' && !$loggerCompany->chat_flg)
                                        @continue
                                    @endif
                                    @if($groupName == '長期保管' && $companyEdition == App\Http\Utils\AppUtils::CONTRACT_EDITION_GW)
                                        @continue
                                    @endif
                                    @if($groupName == '経費精算' && !$loggerCompany->expense_flg)
                                        @continue
                                    @endif
                                    @if($groupName == '長期保管' && !$loggerCompany->long_term_storage_flg)
                                        @continue
                                    @endif
                                    <tr class="row-group">
                                        <td colspan="5">{{ $groupName }}</td>
                                    </tr>
                                    @foreach($arrPermission[$groupName] as $menu => $actions)
                                        @if($groupName == 'ササッと明細')
                                            @php
                                              $authCode = $groupName.':'.$menu;
                                            @endphp
                                        @else
                                            @php
                                              $authCode = $menu;
                                            @endphp
                                        @endif
                                        @if(($menu == '文書登録' || $menu == '連携承認') && !$loggerCompany->special_receive_flg)
                                            @continue
                                        @endif
                                        @if($menu == '連携申請' && !$loggerCompany->special_send_flg)
                                            @continue
                                        @endif
                                        <tr class="row-menu">
                                            <td >{{ $menu }}</td>
                                            <td class="text-center">
                                                @if(isset($actions['view']))
                                                    <input type="checkbox" class="actions" ng-click="changePermisson({{$arrAuthority[$authCode]}})"
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->read_authority == 1)
                                                            ng-checked="true"
                                                        @endif
                                                        data-type="view" data-id="{{ $arrAuthority[$authCode]->id }}" />
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(isset($actions['create']))
                                                    <input type="checkbox" class="actions"
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->read_authority !== 1)
                                                            disabled
                                                        @endif
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->create_authority == 1)
                                                            ng-checked="true"
                                                        @endif
                                                        data-type="create" data-id="{{ $arrAuthority[$authCode]->id }}" />
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(isset($actions['update']))
                                                    <input type="checkbox" class="actions"
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->read_authority !== 1)
                                                            disabled
                                                        @endif
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->update_authority == 1)
                                                            ng-checked="true"
                                                        @endif
                                                        data-type="update" data-id="{{ $arrAuthority[$authCode]->id }}" />
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(isset($actions['delete']))
                                                    <input type="checkbox" class="actions"
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->read_authority !== 1)
                                                            disabled
                                                        @endif
                                                        @if(isset($arrAuthority[$authCode]) AND $arrAuthority[$authCode]->delete_authority == 1)
                                                            ng-checked="true"
                                                        @endif
                                                        data-type="delete" data-id="{{ $arrAuthority[$authCode]->id }}" />
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
                <div class="message mt-3"></div>
                @canany([PermissionUtils::PERMISSION_AUTHORITY_DEFAULT_SETTING_UPDATE])
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-default" ng-click="selectAll()" ><i class="far fa-check-square"></i> 全選択</button>
                        <button type="button" class="btn btn-danger" ng-click="deselectAll()"><i class="far fa-window-close"></i> 全選択解除</button>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success" ng-click="save()">
                            <i class="far fa-save"></i> 更新
                        </button>
                    </div>
                </div>
                @endcanany
            </div>
        </div>

@endsection

@push('scripts')
    <script>


         if(appPacAdmin){
            appPacAdmin.controller('SettingsAuthorityController', function($scope, $rootScope, $http){

                $scope.changePermisson = function(arrAuthority){
                    // arrAuthority:DB Permisson record
                    var arrId=arrAuthority.id;
                    var view_check = 0;// 0 : check->uncheck 1 : uncheck->check
                    for(var i=0; i< $(".actions").length; i++){
                        var item = $(".actions")[i],
                            itemId = $(item).data('id'),
                            type = $(item).data('type');
                        if (itemId == arrId && type === "view"){
                            if ($(item).is(":checked")){
                                view_check = 1;
                            }else{
                                view_check = 0;
                            }
                        }
                        if (itemId == arrId && type !== "view"){
                            if (view_check){
                                item.disabled = false;
                            }else{
                                item.disabled = true;
                                item.checked = false;
                            }
                        }
                    }
                }

                $scope.selectAll = function(){
                        for(var i=0; i< $(".actions").length; i++){
                            var item = $(".actions")[i];
                            item.checked = true;
                            item.disabled = false;
                        }
                }

                $scope.deselectAll = function(){
                    for(var i=0; i< $(".actions").length; i++){
                        var item = $(".actions")[i],
                            type = $(item).data('type');
                        item.checked = false;
                        if (type !== "view"){
                            item.disabled = true;
                        }
                    }
                }

                $scope.save = function(){
                    var actions = {};
                    for(var i=0; i< $(".actions").length; i++){
                        var item = $(".actions")[i],
                            id = $(item).data('id'),
                            type = $(item).data('type');
                        if(actions[id] == undefined)  actions[id] = {};

                        if($(item).is(":checked")){
                            actions[id][type] = 1;
                        }else{
                            actions[id][type] = 2;
                        }
                    }
                    hideMessages();
                    $rootScope.$emit("showLoading");

                    $http.post(link_ajax, {'actions': actions})
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $(".message").append(showMessages(event.data.message, 'success', 10000));
                            }
                    });
                }
            })
         }
    </script>
@endpush

@push('styles_after')
    <style>
        table.adminlist{ width: 100%; }
        .row-group{ background: #fcd5b5; }
        .row-group td{     color: #000; font-weight: bold; }
        .row-menu{ }
        .row-menu td{ }
    </style>
@endpush
