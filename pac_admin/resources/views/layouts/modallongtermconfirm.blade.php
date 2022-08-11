@php
    $user = \Auth::user();
    if ($user->mst_company_id){
        $itemsFolder = App\Http\Utils\LongTermFolderUtils::getLongTermFolderTree($user->mst_company_id);
    }else{
        $itemsFolder = null;
    }
@endphp
<div ng-controller="ModalLongTermConfirmController">

        <div class="modal modal-add-stamp mt-5" id="ModalLongTermConfirm" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-<% size %>" data-backdrop="static" data-keyboard="false">
                <form class="modal-content  p-4">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <div class="modal-title control-label" ng-if="title" ng-bind-html="title"></div>
                    </div>                
                    <!-- Modal body -->
                    <div class="modal-body  " ng-if="message" ng-bind-html="message"></div>
                    <div class="col"> </div>

                        @csrf
                        <div class="form-group mr-3">
                            <label for="keyword" class="col-md-5 ">キーワード登録</label>
                                <textarea name="keyword" id="keyword" ng-model="keyword" class="form-control  ml-3"></textarea>
                                <span class="modal-body  " >キーワードを複数登録する場合は改行して下さい。</span>
                        </div>
                    <div ng-if="long_term_storage_option_flg">
                        <div class="modal-body  ">インデックス登録
                        </div>

                        <div class="row justify-content-md-center mb-1"  ng-repeat="item  in indexes" >

                            <select ng-model="$parent.indexes[$index].longterm_index_id" class="col-4 mr-1"  ng-change="onChangeExample($parent.indexes[$index].longterm_index_id,$index)">
                                <option ng-value=" x.id " ng-repeat="x in longTermdatas"><% x.index_name%></option>
                            </select>
                            <input   ng-model="$parent.indexes[$index].value" class="col-5" type="<% item.type==='number'?'text':item.type %>" id="<%'index'+$index%>" ng-blur="ChangeInput(item,$index)">

                            <div class="col-2">
                                <button type="button" class="btn btn-danger"  ng-click="remove(item,$index)"> 　× 　</button>
                            </div>
                        </div>
                        <div class=" modal-body">
                            <div class="btn btn-primary" ng-if="btnCancel" ng-click="confirmAdd()">
                                <% btnAdd %>
                            </div>
                        </div>
                    </div>

                    <div ng-show="long_term_folder_flg && isFirstSave">
                        <div class="modal-body">フォルダを選択</div>
                        <div class="message message-info"></div>
                        @if($itemsFolder)
                            <div class="col-md-12" style="margin-top: -20px;">
                            <ul class="items tree mt-3" id="sortable_depart" style="overflow-x: auto;">
                                <li class="tree-node parent">
                                    <div class="name " data-id="0" data-longTermFolder="company_name" data-parent="NULL" ng-class="{selected: selectedID == 0}" ng-click="selectRow(0)">
                                            <span class="arrow">
                                                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i>
                                            </span>
                                        <i class="far fa-folder"></i>
                                        <% company_name %>
                                    </div>
                                    <ul class="items">
                                        @foreach ($itemsFolder as $item)
                                            @include('Circulars.folder_tree_node',['itemFolder' => $item])
                                        @endforeach
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>

                    <table class="col" ng-if="selectEnable" style="padding: 10px; align:center;">
                        <div class="col">
                            <tr ng-if="fileContent">
                                <td><label>　<% fileContent %></label></td>
                            </tr>
                        </div>
                        <div class="col">
                            <tr ng-if="fileContent">
                                <td><label>　<% dateContent %></label></td>
                            </tr>
                        </div>
                    </table>
                    <div class="modal-footer">

                        <div class="btn btn-default" ng-if="btnCancel" ng-click="confirmCancel()">
                            <i class="fas fa-times-circle"></i> <% btnCancel %>
                        </div>
                        <div class="btn btn-success" ng-if="btnSuccess" ng-click="confirmSuccess()">
                            <i class="fas fa-check"></i> <% btnSuccess %></div>
                        <div class="btn btn-danger" ng-if="btnDanger" ng-click="confirmDanger()">
                            <i class="fas fa-check"></i> <% btnDanger %>
                        </div>
                        <div class="btn btn-warning" ng-if="btnWarning" ng-click="confirmWarning()">
                            <i class="fas fa-check"></i><% btnWarning %>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    
    
@push('scripts')
    <script>
        var appPacAdmin = initAngularApp();
        if(appPacAdmin){
            appPacAdmin.controller('ModalLongTermConfirmController', function($scope, $rootScope,$timeout){
                $rootScope.$on("showLongTermMocalConfirm", function(event, data){
                    data = data || {};
                    $scope.title = data.title || "";
                    $scope.message = data.message || "";
                    $scope.btnCancel = data.btnCancel || "キャンセル";
                    $scope.btnAdd = data.btnAdd || '　＋　';
                    $scope.btnSuccess = data.btnSuccess || null;
                    $scope.btnDanger = data.btnDanger || null;
                    $scope.btnWarning = data.btnWarning || null;
                    $scope.callCancel = data.callCancel || null;
                    $scope.callSuccess = data.callSuccess || null;
                    $scope.callDanger = data.callDanger || null;
                    $scope.callWarning = data.callWarning || null;
                    $scope.databack = data.databack || null;
                    $scope.size = data.size || "sm";
                    $scope.inputEnable = data.inputEnable || null;
                    $scope.inputMaxLength = data.inputMaxLength || 50;
                    $scope.selectEnable = data.selectEnable || null;
                    $scope.fileContent = data.fileContent || null;
                    $scope.dateContent = data.dateContent || null;
                    $scope.titleItems = data.titleItems || null;
                    $scope.longTermdatas = data.longTermdatas || null;
                    $scope.indexes = data.indexes || null;
                    $scope.keyword=data.keyword|| '';
                    $scope.long_term_storage_option_flg=data.long_term_storage_option_flg||0;
                    $scope.long_term_folder_flg = data.long_term_folder_flg || 0;
                    $scope.company_name = data.company_name || '企業名';
                    $scope.folder_id = data.folder_id || null;
                    $scope.isFirstSave = data.isFirstSave;
                    $scope.selectRow(0);
                    $("#ModalLongTermConfirm").modal();
                });

                $scope.confirmAdd = function(){
                    if($scope.callCancel) $scope.callCancel($scope.databack);
                    $rootScope.$emit('addLongTermIndex',{longterm_index_id: '', value: "", type: "",index_name:'',data_type:''})
                }
                $scope.remove = function(item,index){
                    if($scope.callCancel) $scope.callCancel($scope.databack);
                    $rootScope.$emit('removeLongTermIndex',index)
                }
                $scope.confirmCancel = function(){
                    if($scope.callCancel) $scope.callCancel($scope.databack);
                    $("#ModalLongTermConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                }
                $scope.confirmSuccess = function(){
                    if ($scope.long_term_folder_flg && $scope.selectedID == null){
                        $("#ModalLongTermConfirm .message-info").append(showMessages(['フォルダを選択してください。'], 'danger', 10000));
                        return;
                    }
                    if($scope.inputEnable) $scope.databack = { 'keyword': $('#keyword') , 'folderId': $scope.folder_id};
                    if($scope.callSuccess) $scope.callSuccess($scope.databack);
                    $("#ModalLongTermConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                }

                $scope.confirmDanger = function(){
                    if($scope.callDanger) $scope.callDanger($scope.databack);
                    $("#ModalLongTermConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                }

                $scope.confirmWarning = function(){
                    if($scope.callWarning) $scope.callWarning($scope.databack);
                    $("#ModalLongTermConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                }
                // change=> blur
                // $scope.timer=null;
                $scope.ChangeInput=function (item,index){
                    if(item.data_type===0){
                        // $timeout.cancel($scope.timer);
                        // $scope.timer= $timeout(function (){
                        //     $scope.indexes[index].value=filterNum($scope.indexes[index].value.replace(/[^\d.]/g,''))
                        // },500)
                        $scope.indexes[index].value=filterNum($scope.indexes[index].value.replace(/[^\d.]/g,''))
                    }
                }
                $scope.onChangeExample= function (longterm_index_id,index) {
                    for(var long in this.longTermdatas) {
                        if ($scope.longTermdatas[long].id == longterm_index_id) {
                            $scope.indexes[index].type = $scope.longTermdatas[long].vsInputType;
                            $scope.indexes[index].data_type = $scope.longTermdatas[long].data_type;
                            $scope.indexes[index].index_name=$scope.longTermdatas[long].index_name;
                            if($scope.indexes[index].value && $scope.indexes[index].data_type===0){
                                $scope.indexes[index].value =filterNum($scope.indexes[index].value)
                            }
                            if($scope.indexes[index].type == 1){
                                $scope.indexes[index].value = '';
                            }
                        }
                    }
                }

                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                    $scope.folder_id = $scope.selectedID;
                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }
        function filterNum(num){
            num= (num+'').replace(/,/g,"");
            num = num.split(".");
            var arr = num[0].split("").reverse();
            var res = [];
            for (var i = 0, len = arr.length; i < len; i++) {
                if (i % 3 === 0 && i !== 0) {
                    res.push(",");
                }
                res.push(arr[i]);
            }
            res.reverse();
            if (num[1]) {

                res = res.join("").concat("." + num[1]);
            } else {
                res = res.join("");
            }
            const regexp=/(?:\.0*|(\.\d+?)0+)$/
            return res.replace(regexp,'$1')
        }
    </script>
@endpush

@push('styles_after')
    <style>
        .remove{
            position: relative;
            top: -7px;
        }
        .name{
            background-color: #ffffff;
        }
        .name.selected{
            background-color: #beebff;
        }

    </style>
@endpush