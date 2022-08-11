<div ng-controller="ListController">
    <div class="setting-message message-list mt-3"></div>
    <div class="card mt-3">
            <div class="card-header">外部連携Box</div>
            <div class="card-body form-horizontal">
                <div class="row">
                    <label for="box_auto_storage" class="control-label col-md-2">自動保管：</label>
                    <div class="col-md-3">
                        <label>
                            <input type="checkbox" ng-model="auto_storage_setting.box_enabled_automatic_storage" ng-true-value="1" id="box_enabled_automatic_storage" />
                            有効にする
                        </label>
                    </div>
                    <label class="control-label col-md-3">出力ファイル：</label>
                    <div class="col-md-3">
                        @if($company->esigned_flg)
                            <div>
                                <label>
                                    <input type="checkbox" ng-model="auto_storage_setting.box_enabled_output_file_3" ng-true-value="1" id="box_enabled_output_file_3" />
                                    署名あり・捺印履歴なし
                                </label>
                            </div>
                        @else
                            <div>
                                <label>
                                    <input type="checkbox" ng-model="auto_storage_setting.box_enabled_output_file_1" ng-true-value="1" id="box_enabled_output_file_1" />
                                    署名なし・捺印履歴なし
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <label for="validity_period" class="control-label col-md-2">保管先フォルダ：</label>
                    <div class="col-md-6">
                        <input type="text" class="form-control w-50" style="display: inline-block;font-family: none;" ng-value="auto_storage_setting.box_enabled_folder_to_store" id="box_enabled_folder_to_store" />
                        <input type="button" class="btn btn-success margin-left-5" id="select_box_folder" ng-click="select_box_folder()" value="選択" />
                    </div>
                    <div class="col-md-3">
                        @if($company->esigned_flg)
                            <div>
                                <label>
                                    <input type="checkbox" ng-model="auto_storage_setting.box_enabled_output_file_4" ng-true-value="1" id="box_enabled_output_file_4" />
                                    署名あり・捺印履歴あり
                                </label>
                            </div>
                        @else
                            <div>
                                <label>
                                    <input type="checkbox" ng-model="auto_storage_setting.box_enabled_output_file_2" ng-true-value="1" id="box_enabled_output_file_2" />
                                    署名なし・捺印履歴あり
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6"></div>
                    <label for="enabled_automatic_delete" class="control-label col-md-2">保管後の自動削除：</label>
                    <div class="col-md-3">
                        <label>
                            <input type="checkbox" ng-model="auto_storage_setting.box_enabled_automatic_delete" ng-true-value="1" id="box_enabled_automatic_delete"/>
                            有効にする
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-11"></div>
                    <div class="btn btn-success mb-1" ng-click="save_auto_storage_setting()"><span class="far fa-save"></span> 更新</div>
                </div>
            </div>
        </div>

    <form action="" name="autoStorageDeleteForm" method="GET">
        <div class="card mt-3">
            <div class="card-header">自動削除リスト</div>
            <div class="card-body form-search form-vertical form-horizontal">
                <div class="row form-group">
                    <label for="search-text" class="col-md-2 control-label">検索文字列</label>
                    <div class="col-md-6">
                        <input type="text" name="search-text" value="{{Request::get('search-text', '')}}" class="form-control" placeholder="ファイル名、件名、申請者氏名（部分一致）" id="search-text">
                        {{--<span class="error email-error"></span>--}}
                    </div>
                    <div class="col-lg-4 col-md-4"></div>
                </div>
                <div class="row form-group">
                    <label class="col-md-2 control-label" >状態</label>
                    <div class="col-md-3">
                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\BoxUtils::STATE_BOX_AUTO_STORAGE_LABEL , 'auto-storage-state', Request::get('auto-storage-state', ''),'',['class'=> 'form-control']) !!}
                    </div>
                    <div class="col-md-7"></div>
                </div>
                <div class="row form-group">
                    <label class="col-md-2 control-label" >削除</label>
                    <div class="col-md-3">
                        {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\BoxUtils::STATE_BOX_AUTO_DELETE_LABEL , 'auto-delete-state', Request::get('auto-delete-state', ''),'',['class'=> 'form-control']) !!}
                    </div>
                    <div class="col-md-7"></div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-success mb-1" ng-disabled="numChecked==0" ng-click="re_save_auto_storage()"><i class="fas fa-save" ></i> 再保存</button>
                    <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                    <input type="hidden" class="action" name="action" value="search" />
                </div>
                <div class="message re-message-setting mt-3"></div>
                <div class="row">
                    @if ($auto_delete_lists)
                        <div class="card mt-3 w-100">
                            <div class="card-body">
                                {{--<div class="table-head">--}}
                                    {{--<div class="form-group">--}}
                                        {{--<div class="row">--}}
                                            {{--<label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数: </label>--}}
                                            {{--<div class="col-6 col-md-4 col-xl-1 mb-3">--}}
                                                {{--{!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.autoStorageDeleteForm.submit();']) !!}--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<span class="clear"></span>--}}
                                <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                                    <thead>
                                    <tr>
                                        <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                            <input type="checkbox" onClick="checkAll(this.checked)" />
                                        </th>
                                        <th class="title sort" scope="col">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('申請者', 'email', $orderBy, $orderDir, 'autoStorageDeleteForm') !!}
                                        </th>
                                        <th scope="col"  class="sort">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('ファイル名', 'file_names', $orderBy, $orderDir, 'autoStorageDeleteForm') !!}
                                        </th>
                                        <th scope="col"  class="sort">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('件名', 'title', $orderBy, $orderDir, 'autoStorageDeleteForm') !!}
                                        </th>
                                        <th scope="col"  class="sort">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('保管先フォルダ', 'route', $orderBy, $orderDir, 'autoStorageDeleteForm') !!}
                                        </th>
                                        <th scope="col"  class="sort">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'result', $orderBy, $orderDir, 'autoStorageDeleteForm') !!}
                                        </th>
                                        <th scope="col"  class="sort">
                                            {!! \App\Http\Utils\CommonUtils::showSortColumn('削除', 'circular_status', $orderBy, $orderDir, 'autoStorageDeleteForm') !!}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($auto_delete_lists as $i => $item)
                                        <tr class="row-{{ $item->id }} row-edit">
                                            <td class="title">
                                                <input type="checkbox" value="{{ $item->id }}"  class="cid" onClick="isChecked(this.checked)" />
                                            </td>
                                            <td class="title">{{ $item->applied_email }}[{{ $item->applied_name }}]</td>
                                            <td class="title">{{ $item->file_name }}</td>
                                            <td class="title">{{ $item->title }} </td>
                                            <td class="title">{{ $item->route }}</td>
                                            <td class="title">{{ \App\Http\Utils\BoxUtils::STATE_BOX_AUTO_STORAGE_LABEL[$item->result] }}</td>
                                            <td class="title">{{ $item->circular_status == 9 ? '済' : '未削除' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                @include('layouts.table_footer',['data' => $auto_delete_lists])
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            {{--<div class="message message-list mt-3"></div>--}}
            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
            <input type="hidden" name="page" value="1">
            <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;" />
        </div>
    </form>

    <div class="modal modal-detail" id="folder-from-external-modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <div>
                        <img src="/admin/images/box.svg" alt="box" style="height:40px;"/>
                        <p class="modal-title"><strong>Boxのフォルダを選択</strong></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="box-folder-message"></div>
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    {{--<div class="message"></div>--}}

                    <div class="form-group" style="background-color: #b3d7ff;height: 25px;">
                        <span ng-repeat="(index, value) in folder_parents">
                            <a href="#"><span ng-bind-html="value.name" dd-id="value.id" dd-name="value.name" ng-click="change_parent_folder(value)"></span></a>
                            <span ng-if="(folder_parents.length - 1) != index">/</span>
                        </span>
                    </div>
                    <div class="form-group" style="max-height: 300px;overflow: auto">
                        <table class="w-100 box-folders">
                            <tr class="folder" ng-repeat="(index, value) in folder_items" ng-class="{true: 'selected'}[selected_folder_id == <% value.id %>]" ng-click="click_folder(value)" ng-dblclick="dblclick_folder(value)">
                                <td style="padding: 5px;"><img src="/admin/images/folder.svg" width="35px" height="35px"/></td>
                                <td style="word-wrap:break-word;padding-left: 5px;"><% value.name %></td>
                            </tr>
                        </table>
                    </div>

                </div>

                <!-- Modal footer -->
                <div class="modal-footer" style="flex-wrap: wrap;">
                    <div class="row w-100 mb-3">
                        <label class="col-4 col-md-4 col-xl-4 control-label text-right">ファイル名:</label>
                        <input type="text" class="form-control col-8 col-md-8 col-xl-8 selected_folder_name" ng-value="selected_folder_name"/>
                    </div>
                    <div>
                        <button class="btn btn-default" data-dismiss="modal">
                            キャンセル
                        </button>
                        <button class="btn btn-success" ng-click="create_folder()">
                            フォルダを追加
                        </button>
                        <button class="btn btn-success" ng-click="save_select_folder()">
                            保存先に選択
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        var hasChange = false;
        var selectFolderClick = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.auto_storage_setting = {!! json_encode($auto_storage_setting) !!};
                $scope.selected_folder_id = 0;
                $scope.numChecked = 0;
                $scope.selected_folder_name = "";
                $scope.company = {!! json_encode($company) !!};
                $scope.items = {!! json_encode($auto_delete_lists) !!};

                $scope.select_box_folder = function(){
                    $scope.folder_parents = [{"id": 0, "name": "ルート"}];
                    if(localStorage.getItem('SelectBoxFolder__boxAccessToken')) {

                    }else {
                        window.open(link_list_box+'?drive=box', '_blank');
                    }
                    selectFolderClick = true;
                };

                $scope.show_select_folder = function(){
                    if (localStorage.getItem('SelectBoxFolder__boxAccessToken') && selectFolderClick) {
                        selectFolderClick = false;
                        $rootScope.$emit("showLoading");
                        $http.get(link_get_box_folder_list + '?drive=box&folder_id=0').then(
                            function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    // todo
                                    // message
                                    $(".setting-message").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $scope.folder_parents = [{"id": 0, "name": "ルート"}];
                                    $scope.folder_items = event.data.data;
                                    $('#folder-from-external-modal').modal({backdrop: 'static', keyboard: false});
                                }
                                selectFolderClick = false;
                            },
                            function(event){
                                $rootScope.$emit("hideLoading");
                                if(event.status === 401){
                                    localStorage.removeItem('SelectBoxFolder__boxAccessToken');
                                    window.open(link_list_box + '?drive=box', '_blank');
                                    selectFolderClick = true;
                                }else{
                                    $(".setting-message").append(showMessages([event.data.message], 'danger', 10000));
                                    selectFolderClick = false;
                                }
                            }
                        );
                    //クラウドストレージとの連携失敗時にエラーメッセージを表示
                    }else if(localStorage.getItem('SelectBoxFolder__errormessage') && selectFolderClick){
                        message = localStorage.getItem('SelectBoxFolder__errormessage');
                        $(".setting-message").append(showMessages([message], 'danger', 10000));
                        localStorage.removeItem('SelectBoxFolder__errormessage');
                        selectFolderClick = false;
                    }
                };

                $scope.click_folder = function(value){
                    $scope.selected_folder_id = value['id'];
                    $scope.selected_folder_name = value['name'];
                };

                $scope.dblclick_folder = function(value){
                    $rootScope.$emit("showLoading");
                    $http.get(link_get_box_folder_list + '?drive=box&folder_id=' + value['id']).then(
                        function(event) {
                            if(event.data.status == false){
                                $(".box-folder-message").append(showMessages([event.data.message], 'danger', 10000));
                            }else{
                                $scope.folder_parents.push({"id": value['id'], "name": value['name']});
                                $scope.folder_items = event.data.data;
                            }
                            $rootScope.$emit("hideLoading");
                        },
                        function(event){
                            $rootScope.$emit("hideLoading");
                            // if(event.status === 401){
                            //     localStorage.removeItem('SelectBoxFolder__boxAccessToken');
                            //     window.open(link_list_box + `?drive=box`, '_blank');
                            // }else{
                            //     $(".box-folder-message").append(showMessages(event.data.message, 'danger', 10000));
                            // }
                        }
                    );
                };

                $scope.change_parent_folder = function(value){
                    $rootScope.$emit("showLoading");
                    $http.get(link_get_box_folder_list + '?drive=box&folder_id=' + value['id']).then(
                        function(event) {
                            if(event.data.status == false){
                                $(".box-folder-message").append(showMessages([event.data.message], 'danger', 10000));
                            }else{
                                //$scope.folder_parents.push({"id": value['id'], "name": value['name']});
                                let new_folder_parents = [];
                                for(let i = 0; i < $scope.folder_parents.length; i++){
                                    new_folder_parents.push($scope.folder_parents[i]);
                                    if($scope.folder_parents[i].id == value['id']){
                                        break;
                                    }
                                }
                                $scope.folder_parents = new_folder_parents;
                                $scope.folder_items = event.data.data;
                            }
                            $rootScope.$emit("hideLoading");
                        },
                        function(event){
                            $rootScope.$emit("hideLoading");
                            // if(event.status === 401){
                            //     localStorage.removeItem('SelectBoxFolder__boxAccessToken');
                            //     window.open(link_list_box + `?drive=box`, '_blank');
                            // }else{
                            //     $(".box-folder-message").append(showMessages(event.data.message, 'danger', 10000));
                            // }
                        }
                    );
                };

                $scope.save_select_folder = function(){
                    let parent_path = "すべてのファイル";
                    for(let i = 1; i < $scope.folder_parents.length; i++){
                        parent_path = parent_path + "\\" + $scope.folder_parents[i]['name'];
                    }
                    if($scope.selected_folder_id == $scope.folder_parents[$scope.folder_parents.length - 1]['id']){
                        $scope.auto_storage_setting.box_enabled_folder_to_store = parent_path;
                    }else{
                        $scope.auto_storage_setting.box_enabled_folder_to_store = parent_path + "\\" + $scope.selected_folder_name;
                    }
                    $scope.auto_storage_setting.box_auto_save_folder_id = $scope.selected_folder_id;
                    $('#folder-from-external-modal').modal('hide');
                };

                // 外部連携Box 設定更新
                $scope.save_auto_storage_setting = function(){
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    // 設定check
                    if($scope.auto_storage_setting.box_enabled_automatic_storage == 1){
                        // 自動保管が有効の場合、
                        if($scope.auto_storage_setting.box_enabled_output_file_1 == 0 && $scope.auto_storage_setting.box_enabled_output_file_2 == 0 &&
                            $scope.auto_storage_setting.box_enabled_output_file_3 == 0 && $scope.auto_storage_setting.box_enabled_output_file_4 == 0){
                            $rootScope.$emit("hideLoading");
                            $(".setting-message").append(showMessages(['自動保管を有効にする場合、1つ以上出力ファイルを選択してください'], 'danger', 10000));
                            return;
                        }
                        if($scope.auto_storage_setting.box_enabled_folder_to_store === null || $scope.auto_storage_setting.box_auto_save_folder_id === ""){
                            $rootScope.$emit("hideLoading");
                            $(".setting-message").append(showMessages(['自動保管を有効にする場合、自動保存のフォルダを選択してください'], 'danger', 10000));
                            return;
                        }
                    }
                    $http.post(link_save_auto_storage_setting, $scope.auto_storage_setting)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".setting-message").append(showMessages([event.data.message], 'danger', 10000));
                            }else{
                                $(".setting-message").append(showMessages([event.data.message], 'success', 10000));
                            }
                        });
                };

                // フォルダを追加
                $scope.create_folder = function(){
                    hideMessages();
                    $rootScope.$emit("showLoading");
                    $scope.selected_folder_name = $('.selected_folder_name').val();
                    // 重複check
                    for(var i = 0; i < $scope.folder_items.length-1; i++){
                        if($scope.selected_folder_name.trim() == $scope.folder_items[i]['name'].trim()){
                            $rootScope.$emit("hideLoading");
                            $(".box-folder-message").append(showMessages(['重複したフォルダがあります'], 'danger', 10000));
                            return;
                        }
                    }
                    $http.post(link_create_folder, {"name": $scope.selected_folder_name, "parent_id": $scope.folder_parents[$scope.folder_parents.length-1].id})
                        .then(
                            function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".box-folder-message").append(showMessages([event.data.message], 'danger', 10000));
                                }else{
                                    $(".box-folder-message").append(showMessages([event.data.message], 'success', 10000));
                                    $http.get(link_get_box_folder_list + '?drive=box&folder_id=' + $scope.folder_parents[$scope.folder_parents.length-1].id).then(
                                        function(event) {
                                            if(event.data.status == false){
                                                $(".box-folder-message").append(showMessages([event.data.message], 'danger', 10000));
                                            }else{
                                                $scope.folder_items = event.data.data;
                                            }
                                            $rootScope.$emit("hideLoading");
                                        },
                                        function(event){
                                            $(".box-folder-message").append(showMessages([event.data.message], 'danger', 10000));
                                            $rootScope.$emit("hideLoading");
                                        }
                                    );
                                }
                            },
                            function(event){
                                $(".box-folder-message").append(showMessages([event.data.message], 'danger', 10000));
                                $rootScope.$emit("hideLoading");
                            }
                        );
                };
                $scope.re_save_auto_storage = function(){
                    $rootScope.$emit("hideLoading");
                    var cids = [];
                    for(var i =0; i < $(".cid:checked").length; i++){
                        cids.push($(".cid:checked")[i].value);
                    }
                    const autoDeleteLists =  $scope.items.data;
                    const ids = cids;
                    let chkFlg = true;
                    let msg = '';
                    if(Array.isArray(autoDeleteLists)){
                        autoDeleteLists.forEach(item=>{
                            if(Array.isArray(ids)){
                                ids.forEach(id=>{
                                    if(item.id == id){
                                        if(item.result == 1) {
                                            chkFlg = false;
                                            msg = 'Box自動保管失敗ファイルを選択してください。';
                                        }
                                    }
                                })
                            }
                        })
                    }
                    if (!chkFlg) {
                        $(".re-message-setting").append((showMessages([msg], 'warning', 5000)));
                        return;
                    }
                    $http.post(link_re_save_auto_storage,{"cids": cids})
                        .then(function(event) {
                            if(event.data.status == false){
                                $(".re-message-setting").append(showMessages([event.data.message], 'danger', 10000));
                            }else{
                                $(".re-message-setting").append(showMessages([event.data.message], 'success', 10000));
                            }
                        });
                };
                setInterval($scope.show_select_folder, 1000);
            });
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

@push('styles_after')
<style>    
    .select2-container .select2-selection{
        display: block;
        width: 100%;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{ height: 36px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered{     line-height: 24px; }
    #folder-from-external-modal .folder{
        cursor: pointer;
    }
    #folder-from-external-modal .box-folders .folder{
        color: rgb(31,116,255);
        padding: 5px;
        border-bottom: 1px solid #dee2e6;
    }
    #folder-from-external-modal .box-folders .selected{
        background-color: #0984e3;
        color: white;!important;
    }
    #folder-from-external-modal .box-folders tr:focus{
        background-color: #00b0ff;
    }
</style>
@endpush