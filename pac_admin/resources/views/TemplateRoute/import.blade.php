<div ng-controller="ImportController">

    <div class="modal modal-add-stamp mt-5 modal-child" id="modalImport" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-if="!showResult">CSV取込(承認ルート)</h4>
                    <h4 class="modal-title" ng-if="showResult">CSV取込結果</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-import"></div>
                    <div class="sapo">
                        <div ng-if="!showResult">
                            <div>追加する承認ルートを記載したCSVファイルを指定してください。</div>
                            <br>
                            <div>CSV形式:</div>
                            <div>　ID（※）、名称、有効（０：無効、１：有効）、（※繰り返し）｛部署、役職、合議方法（１：全員指定、２：人数指定）、合議人数（※）｝</div>
                            <br>
                            <div>※ID</div>
                            <div>　空欄場合は新規登録、ある場合は承認ルート更新。</div>
                            <div>※合議人数</div>
                            <div>　合議方法が１の場合は空欄、２の場合は数字を入れる。</div>
                            <div>※取込に利用するファイルは、Shift-JIS形式で保存されたCSV(カンマ区切り)ファイルをご用意ください。</div>
                            <div>※データに不備がある承認ルートは更新、または、登録を行うことはできません。</div>
                            <p></p>
                            <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept=".csv">
                            <div class="text-center">
                                <label><button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-file-import"></span> 取込</button></label>
                                <label><button type="button" class="btn btn-primary" onclick="location.href='import-history?type=2'"><span class="fas fa-file-import"></span>csv取込履歴一覧</button></label>
                            </div>
                        </div>
                        <div ng-if="showResult">
                            <div ng-bind-html="result.message"></div>
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
        //var hasImport = false;
        if(appPacAdmin){
            appPacAdmin.controller('ImportController', function($scope, $rootScope, $http ) {
                $scope.range_func = function(n) {
                    return new Array(n);
                };

                $rootScope.$on("showModalImport", function(event){
                    $scope.showResult = false;
                    hideMessages();
                });

                $scope.showResult = false;
                $scope.result = {total: 0, num_insert: 0, num_update: 0, num_error: 0 , num_normal : 0};

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
                         
                        $http.post(link_import, fd, { headers: { 'Content-Type': undefined }, })
                        .then(function(event) {
                            $scope.result = {message: event.data.message};
                            $scope.showResult = true;
                            $rootScope.$emit("hideLoading");
                        });
                        $('#import_file').val("");
                        $rootScope.$emit("showLoading",{'ttl': 99999999}); // 1day = 86400000ms
                    }
                };
            })
        }
    </script>
@endpush