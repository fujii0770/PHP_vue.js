<div ng-controller="ImportController">

    <div class="modal modal-add-stamp mt-5 modal-child" id="modalImport" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-if="!showResult&&showTabDep">CSV取込(部署登録)</h4>
                    <h4 class="modal-title" ng-if="!showResult&&!showTabDep">CSV取込(役職登録)</h4>
                    <h4 class="modal-title" ng-if="showResult">CSV取込結果</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-import"></div>
                    <div class="sapo">
                        <div ng-if="!showResult">
                            <div ng-if="showTabDep">追加する部署情報を記載したCSVファイルを指定してください。</div>
                            <div ng-if="!showTabDep">追加する役職情報を記載したCSVファイルを指定してください。</div>
                            <br>
                            <div>CSV形式:</div>
                            <div ng-if="showTabDep">　部署ID,部署名(※),動作モード(1:登録,2:更新)</div>
                            <div ng-if="!showTabDep">　役職</div>
                            <br>
                            <div ng-if="showTabDep">※部署名 親部署と子部署を「＞」（全角）で連結(例: 親部署＞子部署)</div>
                            <div>※取込に利用するファイルは、Shift-JIS形式で保存されたCSV(カンマ区切り)ファイルをご用意ください。</div>
                            <div>※1件でもデータに不備があった場合は、すべてのデータを取り込まずに終了します。不備を修正して、再取込してください。</div>
                            <p></p>
                            <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept=".csv">
                            <div class="text-center">
                                <button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-file-import"></span> 取込</button>
                            </div>
                        </div>
                        <div ng-if="showResult">
                            <div>取込が正常に完了しました。</div>
                            <div> <span class="width10 text-right inline-block">正常　件数</span>  ：<% result.num_normal %>件</div>
                            <div> <span class="width10 text-right inline-block">（追加）</span>：<% result.num_insert %>件</div>
                            <div> <span class="width10 text-right inline-block">（更新）</span>：<% result.num_update %>件</div>
                            <div> <span class="width10 text-right inline-block">エラー件数</span>：<% result.num_error %>件</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times-circle"></i> 閉じる</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        var hasImport = false;
        if(appPacAdmin){
            appPacAdmin.controller('ImportController', function($scope, $rootScope, $http ) {

                $rootScope.$on("showModalImport", function(event,data){
                    // false:役職(Position)   true:部署(Department)
                    if(data.type == 'Position'){
                        $scope.showTabDep =false;
                    }else{
                        $scope.showTabDep =true;
                    }
                    $scope.showResult = false;
                    hasImport = false;
                    hideMessages();
                });

                $scope.showResult = false;
                $scope.result = {total_import: 0, num_insert: 0, num_update: 0, num_error: 0 , num_normal : 0 };

                $scope.SelectFile = function($event){
                    if($event.target.files.length){
                        var regex = /(.csv|.txt)$/;
                        if (!regex.test($event.target.value.toLowerCase())) {
                            $("#modalImport .message-import").append(showMessages(['有効なCSVファイルをアップロードしてください。'], 'danger', 10000));
                            $('#import_file').val("");
                            return;
                        }

                        var fd = new FormData();
                        fd.append('file', $event.target.files[0]);
                        if($scope.showTabDep){
                            // 部署(Department)
                            $http.post(link_dep_import, fd, { headers: { 'Content-Type': undefined }, })
                                .then(function(event) {
                                    $scope.result = {total: event.data.total, num_insert: event.data.num_insert, num_update: event.data.num_update, num_error: event.data.num_error , num_normal: event.data.num_normal };
                                    if(event.data.status == false){
                                        $("#modalImport .message-import").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        if(event.data.total != event.data.num_error)
                                            hasImport = true;
                                    }
                                    $scope.showResult = true;
                                });
                        }else{
                            // 役職(Position)
                            $http.post(link_pos_import, fd, { headers: { 'Content-Type': undefined }, })
                                .then(function(event) {
                                    $scope.result = {total: event.data.total, num_insert: event.data.num_insert, num_update: event.data.num_update, num_error: event.data.num_error , num_normal: event.data.num_normal };
                                    if(event.data.status == false){
                                        $("#modalImport .message-import").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        if(event.data.total != event.data.num_error)
                                            hasImport = true;
                                    }
                                    $scope.showResult = true;
                                });
                        }

                        $('#import_file').val("");
                    }
                };
            })
        }

        $("#modalImport").on('hide.bs.modal', function () {
             if(hasImport){
                 location.reload();
             }
        });
    </script>
@endpush