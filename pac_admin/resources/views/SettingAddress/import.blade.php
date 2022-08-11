@push('scripts')
<script defer type="text/javascript" src="{{ asset('/js/libs/angularjs/1.3.11/angular-route.js') }}"></script>
<script defer src="{{ asset('/js/libs/angularjs/1.3.2/angular-resource.min.js') }}"></script>
<script defer src="{{asset('js/pagination/dirPagination.js')}}"></script>
@endpush
<div ng-controller="ImportAddressController">

    <div class="modal modal-add-stamp mt-5 modal-child" id="modalImport" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">            
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" ng-if="!showResult">CSV取込(共通アドレス帳)</h4>
                    <h4 class="modal-title" ng-if="showResult">CSV取込結果</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>                
                <!-- Modal body -->
                <div class="modal-body form-horizontal">
                    <div class="message message-import"></div>
                    <div class="sapo">
                        <div ng-if="!showResult">
                            <div>登録する共通アドレス帳データを記載したCSVファイルを指定してください。</div>
                            <br>
                            <div>CSV形式: メールアドレス,姓,名,会社名,役職</div>
                            <br>
                            <div>※取込に利用するファイルは、Shift-JIS形式で保存されたCSV（カンマ区切り）ファイルをご用意ください。</div>
                            <div>※CSVファイルを取り込むと、既存の共通アドレス帳は全件削除され、取り込んだ内容が新たに登録されます。</div>
                            <div>※１件でもデータに不備があった場合は、全てのデータを取り込まずに終了します。　不備を修正して、再取込してください。</div>
                            <p></p>
                            <input id="import_file" type="file" class="hide" onChange="angular.element(this).scope().SelectFile(event)" accept=".csv">
                            <div class="text-center">
                                <button type="button" class="btn btn-success" onclick="$('#import_file').click()"><span class="fas fa-file-import"></span> 取込</button>
                            </div>
                        </div>
                        <div ng-if="showResult">
                            <div> <span class="text-right inline-block">取込が正常に完了しました。</span></div>
                            <div> <span class="text-right inline-block">正常　件数</span>  ：<% result.num_insert %>件</div>
                            <div> <span class="text-right inline-block">エラー件数</span>：<% result.num_error %>件</div>
                            <div class="card mt-3" ng-if="result.num_error > 0">
                                <div class="card-header">取込エラー一覧</div>
                                <div class="card-body">
                                    <div class="table-head">
                                        <div class="limit-errors">
                                            <span >表示件数: </span>
                                            <select ng-model="limit" ng-options="option for option in option_limit track by option"></select>
                                        </div>
                                    </div>
                                    <span class="clear"></span>

                                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5">
                                        <thead>
                                            <tr>
                                                <th scope="col">行</th>
                                                <th scope="col">列</th>                                     
                                                <th scope="col">項目名</th>
                                                <th scope="col">エラー内容</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr  ng-repeat="err in errors | startFrom:currentPage*limit | limitTo:limit">
                                                <td><% err.row %></td>
                                                <td><% err.col %></td>
                                                <td><% err.name_error %></td>
                                                <td><% err.error %></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="pagination-center">
                                        <div class="pagination">
                                            <button ng-disabled="currentPage == 0" ng-click="currentPage=currentPage-1">
                                                <i class="fas fa-backward"></i>
                                            </button>
                                            <%currentPage+1 %>/<% Math.ceil(count/limit) %>
                                            <button ng-disabled="currentPage >= count/limit - 1" ng-click="currentPage=currentPage+1">
                                                <i class="fas fa-forward"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                </div>
                                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                            </div>
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
            appPacAdmin.controller('ImportAddressController', function($scope, $rootScope, $http ) {
                $rootScope.$on("showModalImport", function(event){
                    $scope.showResult = false;
                    hasImport = false;
                    hideMessages();
                });

           
                $scope.showResult = false;
                $scope.result = {total_import: 0, num_insert: 0, num_update: 0, num_error: 0 };
                $scope.limit = 10;              
                $scope.option_limit = [10,50,100 ];

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
                            $scope.result = {total: event.data.total, num_insert: event.data.num_insert, num_update: event.data.num_update, num_error: event.data.num_error };
                            $scope.errors = event.data.error;
                            $scope.currentPage = 0;
                            $scope.count = event.data.num_error;    
                            $scope.Math = window.Math;          

                            if(event.data.status == false){
                                $("#modalImport .message-import").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                if(event.data.total != event.data.num_error)
                                    hasImport = true;
                            }
                            $scope.showResult = true;
                        });
                        $('#import_file').val("");
                    }
                };
            })

            appPacAdmin.filter('startFrom', function() {
                return function(input, start) {
                    start = +start; //parse to int
                    return input.slice(start);
                }
            });
        }

        

        $("#modalImport").on('hide.bs.modal', function () {
             if(hasImport){
                 location.reload();
             }
        });
    </script>
@endpush