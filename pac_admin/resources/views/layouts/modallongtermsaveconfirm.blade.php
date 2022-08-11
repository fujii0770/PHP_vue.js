@php
    $user = \Auth::user();
    if ($user->mst_company_id){
        $itemsFolder = App\Http\Utils\LongTermFolderUtils::getLongTermFolderTree($user->mst_company_id);
    }else{
        $itemsFolder = null;
    }
@endphp
<div ng-controller="ModalLongTermSaveConfirmController">

    <div class="modal modal-add-stamp mt-5" id="ModalLongTermSaveConfirm" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-<% size %>" data-backdrop="static" data-keyboard="false">
            <form class="modal-content  p-4">
                <!-- Modal Header -->
                <div class="modal-header">
                    <div class="modal-title control-label" ng-if="title" ng-bind-html="title"></div>
                </div>
                <!-- Modal body -->

                <div class="col"> </div>
                <div class="message message-import"></div>
                @csrf
                <div class="form-group" id="drop-zone" style="margin-top: 10px;">
                    <label id="uplabel"
                           for="upfile"
                           class="filelabel" style="text-align:center;"><strong ng-bind="upfilename" style="font-size: 13px;"></strong></label>
                    <input type="file" file-model="file_upload" id="upfile" style="display: none;" accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                </div>
                <input type="hidden" name="base64_file" id="base64_file">
                <div class="form-group">
                    <label for="keyword_upload" class="col-md-5 ">キーワード登録</label>
                    <textarea name="keyword" id="keyword_upload" ng-model="keyword" class="form-control  ml-3"></textarea>
                    <span class="modal-body  " >キーワードを複数登録する場合は改行して下さい。</span>
                </div>
                <div ng-if="long_term_storage_option_flg">
                    <div class="modal-body  ">インデックス登録
                    </div>

                    <div class="row justify-content-md-center mb-1"  ng-repeat="item  in indexess" >

                        <select ng-model="$parent.indexess[$index].longterm_index_id" class="col-4 mr-1"  ng-change="onChangeExample($parent.indexess[$index].longterm_index_id,$index)">
                            <option ng-value=" x.id " ng-repeat="x in longTermdatas"><% x.value%></option>
                        </select>
                        <input   ng-model="$parent.indexess[$index].value" class="col-5" type="<% item.type===2?'date':'text' %>" id="<%'upload_index'+$index%>" ng-blur="ChangeInput(item,$index)">

                        <div class="col-2">
                            <button type="button" class="btn btn-danger"  ng-click="remove(item,$index)"></i> X </button>
                        </div>
                    </div>
                    <div class=" modal-body">
                        <div class="btn btn-primary" ng-if="btnCancel" ng-click="confirmAdd()">
                           </i> <% btnAdd %>
                        </div>
                    </div>
                </div>
                <div ng-show="long_term_folder_flg">
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

</div>


@push('scripts')
    <script>
       var link = "{{ url('circulars-long-term') }}";
        var appPacAdmin = initAngularApp();
        if(appPacAdmin){
            appPacAdmin.controller('ModalLongTermSaveConfirmController', function($scope, $rootScope,$http){
                $rootScope.$on("showLongTermSaveMocalConfirm", function(event, data){
                    data = data || {};
                    $scope.title = data.title || "";
                    $scope.message = data.message || "";
                    $scope.btnCancel = data.btnCancel || "キャンセル";
                    $scope.btnAdd = data.btnAdd || '+';
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
                    $scope.indexess = data.indexess || null;
                    $scope.keyword=data.keyword|| '';
                    $scope.long_term_storage_option_flg=data.long_term_storage_option_flg||0;
                    $scope.files = "";
                    $scope.file_upload = "";
                    $scope.upfilename = "ファイル選択";
                    $scope.long_term_folder_flg = data.long_term_folder_flg || 0;
                    $scope.company_name = data.company_name || '企業名';
                    $scope.folder_id = data.folder_id || null;
                    $scope.selectRow(0);
                    $("#ModalLongTermSaveConfirm").modal();
                    var dropZone = document.getElementById('drop-zone');

                    dropZone.addEventListener('dragover', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                    }, false);

                    dropZone.addEventListener('dragleave', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                    }, false);

                    dropZone.addEventListener('drop', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        this.style.background = '#ffffff';
                        $scope.files = e.dataTransfer.files;
                        $scope.file_upload = $scope.files[0];
                        e.target.value = ""
                        setTimeout(function () {
                            $scope.$apply(function () {
                                console.log('drag-and-drop event');
                            });
                        }, 2)
                    }, false);
                });

                $scope.SelectFile = function(e){
                    if($scope.file_upload == "" || $scope.file_upload == undefined){
                        alert("ファイルが選択されていません");
                        return;
                    }
                        var fd = new FormData();
                        fd.append('file', $scope.file_upload);
                        fd.append('_token',$('input[name="_token"]').val())
                        $rootScope.$emit("showLoading",{'ttl': 99999999});
                        $http.post(link+'/long-term-upload', fd, { headers: { 'Content-Type': undefined }, })
                            .then(function(event) {
                                if(event.data.status == false){
                                    $("#ModalLongTermSaveConfirm .message-import").append(showMessages(event.data.message, 'danger', 10000));
                                    $scope.result = {status:false};
                                }else{
                                    $scope.result = {file_name: event.data.data.file_name, upload_id: event.data.data.upload_id,status:true};
                                    $("#ModalLongTermSaveConfirm .message-import").append(showMessages(event.data.message, 'success', 10000));
                                }
                                $rootScope.$emit("hideLoading");
                                e.target.value = ""
                            });
                };
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
                    $("#ModalLongTermSaveConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                    $('#upload_file').val("");
                    $scope.result = undefined;
                    $scope.folder_id = null;
                    $scope.keyword = '';
                    $scope.file_upload = undefined
                    $('#upfile').val('');
                }
                $scope.confirmSuccess = function(){
                    if ($scope.long_term_folder_flg && $scope.selectedID == null){
                        $("#ModalLongTermSaveConfirm .message-info").append(showMessages(['フォルダを選択してください。'], 'danger', 10000));
                        return;
                    }
                    if($scope.inputEnable) $scope.databack = {'keyword':$scope.keyword,'file':$scope.result,'folderId': $scope.folder_id};
                    if($scope.callSuccess) $scope.callSuccess($scope.databack);
                    $("#ModalLongTermSaveConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                    $('#upload_file').val("");
                }

                $scope.confirmDanger = function(){
                    if($scope.callDanger) $scope.callDanger($scope.databack);
                    $("#ModalLongTermSaveConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                    $('#upload_file').val("");
                }

                $scope.confirmWarning = function(){
                    if($scope.callWarning) $scope.callWarning($scope.databack);
                    $("#ModalLongTermSaveConfirm").modal("hide");
                    $rootScope.$emit('intLongTermIndex')
                    $rootScope.$emit('intKeyword')
                    $rootScope.$emit('intFolderId')
                    $scope.selectRow(0);
                    $('#upload_file').val("");
                }
                $scope.ChangeInput=function (item,index){
                    if(item.type===0){
                        $scope.indexess[index].value=filterNum($scope.indexess[index].value.replace(/[^\d.]/g,''))
                    }
                }

                $scope.$watch(function(){
                    return $scope.file_upload;
                }, function(newValue, oldValue, scope){

                    if($scope.file_upload == "" || $scope.file_upload == undefined){
                        $scope.upfilename = "ファイル選択";
                    }else{
                        $scope.upfilename = $scope.file_upload.name;
                        $scope.SelectFile()
                    }

                });
                $scope.onChangeExample= function (longterm_index_id,index) {
                    for(var long in this.longTermdatas) {
                        if ($scope.longTermdatas[long].id == longterm_index_id) {
                            $scope.indexess[index].type = $scope.longTermdatas[long].type;
                            // $scope.indexess[index].data_type = $scope.longTermdatas[long].data_type;
                            $scope.indexess[index].index_name=$scope.longTermdatas[long].value;
                            if($scope.indexess[index].value && $scope.indexess[index].type===0){
                                $scope.indexess[index].value =filterNum($scope.indexess[index].value)
                            }
                            if($scope.indexess[index].type == 1){
                                $scope.indexess[index].value = '';
                            }
                        }
                    }
                }
                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                    $scope.folder_id = $scope.selectedID;
                }
            })
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
            appPacAdmin.directive('fileModel',function($parse){
                return{
                    restrict: 'A',
                    link: function(scope,element,attrs){
                        var model = $parse(attrs.fileModel);
                        element.bind('change',function(){
                            scope.$apply(function(){
                                model.assign(scope,element[0].files[0]);
                            });
                        });
                    }
                };
            });
        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush

@push('styles_after')
    <style>
        .remove{
            position: relative;
            top: -7px;
        }
        .filelabel{border: 3px dashed; border-color :#33CCFF; padding: 10px; width: 250px; height:55px; border-radius: 10px; margin-left:20px;}
        .filelabel2{border: 3px dashed; border-color :#33CCFF; padding: 10px; width: 1200px; height:100px; border-radius: 10px; margin-left:20px;}
        .dragover{border: 3px solid; border-color :#00FF00;}
        .dragleave{border: 3px solid; border-color :#00FF00;}
        .drop{border: 3px solid; border-color :#00FF00;}
        .name{
            background-color: #ffffff;
        }
        .name.selected{
            background-color: #beebff;
        }
    </style>
@endpush
