@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('LongTerm.LongTermStorage.Store') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>

        <div class="GlobalSetting Settings-long-term-save">
            <div ng-controller="SettingsLongTermSaveController">
                <div class="message"></div>
                <div class="card mt-3">
                    <div class="card-header">長期保管設定</div>
                    <div class="card-body form-horizontal">
                        <div class="form-group row">
                            <div class="col-md-1"></div>
                            <label for="auto_save_1" class="text-right-lg col-md-3">文書の自動保管</label>
                            <div class="col-md-8">
                                <input type="radio" ng-model="company.auto_save" id="auto_save_1" ng-value="1" /> 有効
                                <input type="radio" ng-model="company.auto_save" id="auto_save_0" class="margin-left-10" ng-value="0" /> 無効
                            </div>
                        </div>
                        <div class="form-group row" ng-show="company.long_term_folder_flg && company.auto_save">
                            <div class="col-md-1"></div>
                            <label for="auto_save_1" class="text-right-lg col-md-3">フォルダを選択</label>
                            <div class="col-md-7">
                                <ul class="items tree mt-3" id="sortable_depart" style="overflow-x: auto;">
                                    <li class="tree-node parent">
                                        <div class="name " data-id="0" data-longTermFolder="{{ $company->company_name }}" data-parent="NULL" ng-class="{selected: selectedID == 0}" ng-click="selectRow(0)">
                                            <span class="arrow">
                                                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i>
                                            </span>
                                            <i class="far fa-folder"></i>
                                            {{ $company->company_name }}
                                        </div>
                                        <ul class="items">
                                            @foreach ($itemsFolder as $item)
                                                @include('LongTerm.longTermFolder.folder_tree_node',['itemFolder' => $item])
                                            @endforeach
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-1"></div>
                            <label for="number-of-automatic-storage" class="control-label col-md-3">自動保管件数</label>
                            <div class="col-md-2">
                                <input type="number"  ng-model="company.auto_save_num" id="auto_save_num_1" ng-value="1" min="0"  class="form-control"  />
                            </div>
                            <div class="col-md-5"><span class="help-message margin-left-20">0~500件で設定してください</span></div>
                        </div>
                    {{--     PAC_5-2318 S  --add field long_term_storage_delete_flg 文書の削除--}}
                        <div class="form-group row">
                            <div class="col-md-1"></div>
                            <label for="long_term_storage_delete_flg_1" class="text-right-lg col-md-3">利用者側の削除ボタン</label>
                            <div class="col-md-8">
                                <input type="radio" ng-model="company.long_term_storage_delete_flg" id="long_term_storage_delete_flg_1" ng-value="1" /> 有効
                                <input type="radio" ng-model="company.long_term_storage_delete_flg" id="long_term_storage_delete_flg_0" class="margin-left-10" ng-value="0" /> 無効
                            </div>
                        </div>
                    {{--   PAC_5-2318 end     --}}
                        @if($company->long_term_storage_flg && $company->long_term_storage_option_flg && $company->stamp_flg)
                            <div class="form-group row">
                                <div class="col-md-1"></div>
                                <label for="time_stamp_assign_flg_1" class="text-right-lg col-md-3">利用者側タイムスタンプ再付与機能</label>
                                <div class="col-md-8">
                                    <input type="radio" ng-model="company.time_stamp_assign_flg" id="time_stamp_assign_flg_1" ng-value="1" /> 有効
                                    <input type="radio" ng-model="company.time_stamp_assign_flg" id="time_stamp_assign_flg_0" class="margin-left-10" ng-value="0" /> 無効
                                </div>
                            </div>
                        @endif
                    {{--     PAC_5-3455  --add field long_term_storage_move_flg 文書の移動--}}
                        <div class="form-group row">
                            <div class="col-md-1"></div>
                            <label for="long_term_storage_move_flg_1" class="text-right-lg col-md-3">利用者側の移動ボタン</label>
                            <div class="col-md-8">
                                <input type="radio" ng-model="company.long_term_storage_move_flg" id="long_term_storage_move_flg_1" ng-value="1" /> 有効
                                <input type="radio" ng-model="company.long_term_storage_move_flg" id="long_term_storage_move_flg_0" class="margin-left-10" ng-value="0" /> 無効
                            </div>
                        </div>
                    </div>
                </div>
                @canany([PermissionUtils::PERMISSION_LONG_TERM_STORAGE_SETTING_UPDATE])
                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-success" ng-click="save()" ng-disabled="canUpdate()">
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
            appPacAdmin.controller('SettingsLongTermSaveController', function($scope, $rootScope, $http){
                @if($company)
                $scope.company = {!! json_encode($company) !!};
                @else
                    $scope.company = { auto_save : 0, time_stamp_assign_flg : 0, long_term_storage_flg : 0,
                    long_term_storage_option_flg : 0, stamp_flg : 0,long_term_storage_delete_flg: 0,auto_save_num: 0,long_term_default_folder_id: 0,
                    long_term_storage_move_flg: 0};
                @endif

                $scope.folder = {!! json_encode($folder) !!}

                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else{
                        $scope.selectedID = id;
                    }
                    $scope.company.long_term_default_folder_id = $scope.selectedID;
                };
                //親ファイルの取得
                $scope.openFolderNode = function (folder_id){
                    let className = $('.' + folder_id)[0].className;
                    $('.' + folder_id)[0].className = className + ' open';
                }
                //選択したフォルダを選択
                $scope.showSelectedFloder = function (){
                    if ($scope.folder['folder_id']){
                        if ($scope.folder['parent_folder_id']){
                            let className = $('.parent')[0].className;
                            $('.parent')[0].className = className + ' open';
                            for (let i = 0; i < $scope.folder['parent_folder_id'].length; i++){
                                $scope.openFolderNode($scope.folder['parent_folder_id'][i]);
                            }
                        }
                        $scope.selectRow($scope.folder['folder_id']);
                    }else {
                        $scope.selectRow(0);
                    }
                }

                $scope.showSelectedFloder();

                $scope.save = function(){
                    var actions = [];
                    for(var i=0; i< $(".actions:checked").length; i++){
                        var item = $(".actions:checked")[i];
                        actions.push($(item).val())
                    }

                    hideMessages();
                    $rootScope.$emit("showLoading");

                    $http.post(link_ajax, $scope.company)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $(".message").append(showMessages(event.data.message, 'success', 10000));
                            }
                    });
                }

                $scope.canUpdate = function (){
                    if ($scope.company.auto_save && $scope.company.long_term_default_folder_id != null){
                        return false;
                    }else if (!$scope.company.auto_save){
                        return false;
                    } else return true
                }
            })
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
        .name:hover{
            background-color: #e7f4f9;
        }
        .name.selected{
            background-color: #beebff;
        }

    </style>
@endpush
