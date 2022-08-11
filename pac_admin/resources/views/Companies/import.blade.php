<div ng-controller="ImportController">

    <div class="modal modal-add-stamp mt-5 modal-child" style="padding-right:30px" id="modalImport" data-backdrop="static" data-keyboard="false"> <!-- 詳細modalと微妙に位置がずれるため、暫定的に手打ちで修正 -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-if="!showResult">CSV取込(部署名入り日付印)</h4>
                    <h4 class="modal-title" ng-if="showResult">CSV取込結果</h4>
                    <button type="button" class="close" ng-click="closeImport()">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-import"></div>
                    <div class="sapo">
                        <div ng-if="!showResult">
                            <div>追加する部署名入り日付印情報を記載したCSVファイルを指定してください。</div>
                            <br>
                            <div>CSV形式:</div>
                            <div>　印面種類(1:15.5ミリ日付印,2:21ミリ日付印),レイアウト(1:上下1行,2:上下1行(子付き),3:下2行,4:上2行,5:上下2行),</div>
                            <div>　書体項目(1:楷書,2:古印,3:行書),インキ色(1:紫色,2:赤色,3:藍色,4:黒色,5:朱色,6:緑色),印面文字1(※),印面文字2(※),</div>
                            <div>　印面文字3(※),印面文字4(※),利用者メールアドレス,管理者メールアドレス,タイムスタンプ(0:無効,1:有効)</div>
                            <br>
                            <div>※印面文字 レイアウトに従って必要なテキストを設定します。</div>
                            <div>※取込に利用するファイルは、UTF-8形式で保存されたCSV(カンマ区切り)ファイルをご用意ください。</div>
                            <div>※1件でもデータに不備があった場合は、すべてのデータを取り込まずに終了します。不備を修正して、再取込してください。</div>
                            <p></p>
                            <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept=".csv">
                            <div class="text-center">
                                <button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-file-import"></span> 取込</button>
                            </div>
                        </div>
                        <div ng-if="showResult">
                            <div ng-if="importResult">
                            <div>取込が正常に完了しました。</div>
                            <div> <span class="width10 text-right inline-block">    総件数</span>：<% result.total %>件</div>
                            </div>
                            <div ng-if="!importResult">
                            <div>取込に失敗しました。</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" ng-click="closeImport()"><i class="fas fa-times-circle"></i> 閉じる</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ImportController', function($scope, $rootScope, $http ) {

                $scope.showTabDep =true;

                $scope.showResult = false;
                $scope.importResult = true;
                $scope.result = {total_import: 0, num_insert: 0, num_update: 0, num_error: 0 , num_normal : 0 };
                $scope.id = 0;

                $rootScope.$on("showModalImport", function(event,data){
                    $scope.id = data.id;
                });

                $scope.SelectFile = function($event){
                    $rootScope.$emit("showLoading");
                    if($event.target.files.length){
                        var regex = /(.csv|.txt)$/;
                        if (!regex.test($event.target.value.toLowerCase())) {
                            $rootScope.$emit("hideLoading");
                            $("#modalImport .message-import").append(showMessages(['有効なCSVファイルをアップロードしてください。'], 'danger', 10000));
                            $('#import_file').val("");
                            return;
                        }

                        var fd = new FormData();
                        fd.append('file', $event.target.files[0]);
                        $http.post(link_depStamp_import +"/"+ $scope.id, fd, { headers: { 'Content-Type': undefined }, })
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                $scope.result = {total: event.data.num_total, num_error: event.data.num_error , num_normal: event.data.num_normal };
                                if(event.data.status == false){
                                    $("#modalImport .message-import").append(showMessages(event.data.message, 'danger', 10000));
                                    $scope.importResult = false;
                                }else{
                                    importResult = true;
                                }
                                $scope.showResult = true;
                            })
                            .catch(function(event) {
                                $rootScope.$emit("hideLoading");
                                $("#modalImport .message-import").append(showMessages(['セッションがタイムアウトしました。'], 'danger', 10000));
                                
                            });

                        $('#import_file').val("");
                    }
                };

                $scope.closeImport = function(){
                    $scope.showResult = false;
                    $("#modalImport").modal('hide');
                }
            })
        }

    </script>
@endpush