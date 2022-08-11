<div ng-controller="ImportController">

    <div class="modal modal-add-stamp mt-5 modal-child" id="modalImport" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-if="!showResult&&!without_email_import_flg">CSV取込(受信専用利用者登録)</h4>
                    <h4 class="modal-title" ng-if="!showResult&&without_email_import_flg">無ユーザーCSV取込(受信専用利用者登録)</h4>
                    <h4 class="modal-title" ng-if="showResult">CSV取込結果</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>                
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-import"></div>
                    <div class="sapo">
                        <div ng-if="!showResult&&!without_email_import_flg">
                            <div>追加する利用者情報を記載したCSVファイルを指定してください。</div>
                            <br>
                            <div>CSV形式:</div>
                            <div>　ユーザID(xxx@domain.co.jp形式),通知先メールアドレス,姓,名,部署,役職,郵便番号,住所,電話番号,FAX番号,印面設定(※),印面文字,
                                有効化(1:有効にする),多要素認証(0:無効,1:メール,2:QRコード),備考</div>
                            <br>
                            <div>※印面設定 0:なし、1:氏名印[楷書]、2:氏名印[古印]、3:氏名印[行書]</div>
                            <div>　0を設定した場合は、印面文字も空(null)にしてください。</div>
                            <div>　印面以外の情報のみ登録/更新する場合は、印面設定:0、印面文字:空(null)としてください。</div>
                            <div>※取込に利用するファイルは、Shift-JIS形式で保存されたCSV(カンマ区切り)ファイルをご用意ください。</div>
                            <div>※データに不備がある利用者は更新、または、登録を行うことはできません。</div>
                            <p></p>
                            <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept=".csv">
                            <div class="text-center">
                                <label><button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-file-import"></span> 取込</button></label>
                                <label><button type="button" class="btn btn-primary" onclick="parent.location.href='/admin/import-history?type=4'"><span class="fas fa-file-import"></span>csv取込履歴一覧</button></label>
                            </div>
                        </div>
                        <div ng-if="!showResult&&without_email_import_flg">
                            <div>追加する利用者情報を記載したCSVファイルを指定してください。</div>
                            <br>
                            <div>CSV形式:</div>
                            <div>　ユーザID(xxx@domain.co.jp形式),通知先メールアドレス(空:固定),姓,名,部署,役職,郵便番号,住所,電話番号,FAX番号,印面設定(※),印面文字,
                                有効化(1:有効にする),多要素認証(0:無効,2:QRコード),備考</div>
                            <br>
                            <div>※印面設定 0:なし、1:氏名印[楷書]、2:氏名印[古印]、3:氏名印[行書]</div>
                            <div>　0を設定した場合は、印面文字も空(null)にしてください。</div>
                            <div>　印面以外の情報のみ登録/更新する場合は、印面設定:0、印面文字:空(null)としてください。</div>
                            <div>※取込に利用するファイルは、Shift-JIS形式で保存されたCSV(カンマ区切り)ファイルをご用意ください。</div>
                            <div>※データに不備がある利用者は更新、または、登録を行うことはできません。</div>
                            <p></p>
                            <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept=".csv">
                            <div class="text-center">
                                <label><button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-file-import"></span> 取込</button></label>
                                <label><button type="button" class="btn btn-primary" onclick="parent.location.href='/admin/import-history?type=4'"><span class="fas fa-file-import"></span>csv取込履歴一覧</button></label>
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

                $scope.without_email_import_flg = 0;
                $rootScope.$on("showModalImport", function(event){
                    $scope.showResult = false;
                    $scope.without_email_import_flg = $rootScope.without_email_import_flg;
                    //$scope.showResultDetail = false;
                    //hasImport = false;
                    hideMessages();
                });

                $scope.showResult = false;
                //$scope.showResultDetail = false;
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
                        fd.append('normal_user', '1');
                        fd.append('without_email_import_flg', $rootScope.without_email_import_flg);
                         
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

                $scope.changeDetailPage = function(page){
                    //hideMessages();
                    if(page == $scope.result.error_detail.current_page || page < 1 || page > $scope.result.error_detail.last_page){
                        return;
                    }
                    $http.get(link_import_detail + "/" +$scope.result.csv_import_list_id + "?page=" + page)
                        .then(function(event) {
                            if(event.data.status == false){
                                $("#modalImport .message-import").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.result.error_detail = event.data.error_detail;
                            }
                        });
                };
            })
        }

    </script>
@endpush

<style>
    .errDetail td,tr{
        border: 1px solid black;
    }
    .import-detail-pager .page-item{
        cursor: pointer;
    }
    .import-detail-pager .page-item.active{
        cursor: default;
    }
    .import-detail-pager .page-item.disabled{
        cursor: default;
    }
</style>