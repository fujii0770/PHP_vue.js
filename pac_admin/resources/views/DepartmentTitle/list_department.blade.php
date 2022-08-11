    
    <span class="clear"></span>
    <form class="form_edit_department" action="" method="GET" name="adminFormDepartment">
        <div ng-controller="ListDepartmentController" class="list-department">
            <div class="message message-info mt-3">
                @if (Session::has('message'))
                    <div class="alert alert-success alert-1"><a class="close" aria-label="close">×</a><span><p>{{ Session::get('message') }}</p></span></div>
                @endif
            </div>
            <div class="text-right mt-3">
                @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE])
                    <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
                @endcanany
                @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_UPDATE])
                    <button type="button" class="btn btn-success  mb-1" ng-click="editRecord()" ng-disabled="!selectedID"><i class="fas fa-edit" ></i> 名称変更</button>
                @endcanany
                @can([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_CREATE])
                @can([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_UPDATE])
                <button ng-if="showBtnCsv" type="button" class="btn btn-success mb-1" ng-click="upload()"><i class="fas fa-upload"></i> CSV取込</button>
                @endcan
                @endcan
                <button ng-if="showBtnCsv" type="button" class="btn btn-warning mb-1" ng-click="downloadCsv()"><i class="fas fa-download"></i> CSV出力</button>
                @canany([PermissionUtils::PERMISSION_DEPARTMENT_TITLE_DELETE])            
                    <button type="button" class="btn btn-danger mb-1" ng-click="delete()" ng-disabled="!selectedID"><i class="fas fa-trash-alt"></i> 削除</button>
                @endcanany
            </div>

            <ul class="items tree mt-3" id="sortable_depart">
                @foreach ($itemsDepartment as $item)
                    @include('DepartmentTitle.department_tree_node',['itemDepartment' => $item])
                @endforeach
            </ul>
            <div class="items-download mt-3 mb-3">                
                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                    <thead>
                    </thead>
                    <tbody>
                        {{-- showBtnCsv がCSV出力ボタンの表示条件に関わっているので一部処理を残す --}}
                        <?php $showBtnCsv = 1; ?>
                        @foreach ($departmentDownloadCsv as $item)
                            <?php if($item->state == 0) $showBtnCsv = 0; ?>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <input type="hidden" class="action-department" name="action" value="" />
        <input type="hidden" value="{{ Request::get('departmentCsvOrderBy','DESC') }}" name="departmentCsvOrderBy" />
        <input type="hidden" value="{{ Request::get('departmentCsvOrderDir','DESC') }}" name="departmentCsvOrderDir" />
    </form>
<style>
    .btn-download{ color: #337ab7; cursor: pointer; }
    .btn-download:hover{ text-decoration: underline; }
</style>
@push('scripts')
    <script>
        var hasChangeDepartment = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListDepartmentController', function($scope, $rootScope, $http){ 
                $scope.type = 'department';
                $scope.selectedID = null;
                $scope.showBtnCsv = {{ $showBtnCsv }};
                $scope.changeDepartmentId = null;
                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                };
                $scope.addNew = function(){
                    $rootScope.$emit("openNewDepartment");
                };

                $scope.editRecord = function(){
                    $rootScope.$emit("openEditDepartment",{id:$scope.selectedID});
                 };
                 
                $scope.downloadCsv = function(){

                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'部署データを出力します。実行しますか？',
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax_department_csv, { })
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $(".message").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                        $(".message").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                });
                        }
                    });
                };

                 $scope.downloadFile = function(linkfile){ 
                    $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'CSVファイルをダウンロードします。よろしいですか？', 
                            btnSuccess:'はい',
                            callSuccess: function(){
                                location.href=linkfile;
                            }
                        });
                 };
                 
                 $scope.deleteDownload = function(id){ 
                    $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'CSVファイルを削除します。よろしいですか？', 
                            btnDanger:'はい',
                            databack: id,
                            callDanger: function(id){
                                $rootScope.$emit("showLoading");
                                $http.delete(link_ajax + "/download/" + id)
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".list-"+$scope.type+" .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{                                        
                                            $(".items-download .row-"+id).remove();
                                            $(".list-"+$scope.type+" .message-info").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                 };
                 
                 $scope.delete = function(){    
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択中の部署を削除しますか？', 
                        btnDanger:'はい',
                        databack: $scope.selectedID,
                        callDanger: function(selectedID){
                            $rootScope.$emit("showLoading");
                            $http.delete(link_ajax + "/" + selectedID+"?type="+$scope.type)
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    if(event.data.status == false){
                                        $(".list-"+$scope.type+" .message-info").append(showMessages(event.data.message, 'danger', 10000));
                                    }else{
                                       location.reload();
                                       $(".list-"+$scope.type+" .items .name.selected").parent().remove();
                                    }
                                });
                        }
                    });         

                 };

                $scope.upload = function(){
                    $("#modalImport").modal();
                    $rootScope.$emit("showModalImport",{type:'Department'});
                };
                $scope.updateSort = function(data){
                    $rootScope.$emit("showLoading");
                    $http({
                        method:'POST',
                        url:link_ajax_update_department_sort,
                        data: {
                            sort:data
                        }
                    }).then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $(".list-"+$scope.type+" .message-info").append(showMessages(event.data.message, 'danger', 10000));
                            location.reload();
                        }
                    });
                }
                $('.list-department .items').each(function(){
                    new Sortable($(this).get(0), {
                        animation: 150,
                        group: 'nested',
                        fallbackOnBody: true,
                        ghostClass: 'blue-background-class',

                        onStart: function (/**Event*/evt) {
                            $('.items').each(function (){
                                $(this).css("padding-bottom",'15px')
                                if ($(this).find('li').length==0){
                                    $(this).parent('.tree-node').addClass('open');
                                    $(this).css("min-height",'15px')

                                }
                            })
                        },
                        onEnd: function (/**Event*/evt) {
                            let item = evt.item.getElementsByClassName('name')[0].dataset;
                            $scope.changeDepartmentId = item.id;
                            resetTreeArrow()
                            $('.items').each(function (){
                                $(this).css("padding-bottom",'0')
                                if ($(this).find('li').length==0){
                                    $(this).parent('.tree-node').removeClass('open');
                                    $(this).css("min-height",'0')


                                }
                            })
                        },
                        onChange: function(/**Event*/evt) {
                            // same properties as onEnd
                        }
                    });
                })
                var sortArr={
                    old:[],
                    new:[]
                };
                var buildSortArr=function (ele,arr){
                    ele.children('.tree-node').each(function(index){
                        let obj=$(this).children('.name')
                        let parent=0
                        if (!$(this).parent().hasClass('tree')){
                            parent=$(this).parent().prev().data('id')
                        }
                        arr[obj.data('id')]={id:obj.data('id'),sort:index,parent:parent,
                            department_name:obj.data('department'),change_flg: obj.data('id') ==  $scope.changeDepartmentId ? 1 : 0}
                        buildSortArr($(this).children('.items'),arr)
                    })
                }
                buildSortArr($('#sortable_depart'),sortArr.old)

                var resetTreeArrow=function(){
                    buildSortArr($('#sortable_depart'),sortArr.new)
                    let res=sortArr.new.filter(function(v){
                        return v.sort!=sortArr.old[v.id].sort || v.parent!=sortArr.old[v.id].parent
                    })
                    if (res.length>0){
                        $scope.updateSort(res)
                    }
                    sortArr.old=sortArr.new.slice()
                    $('.list-department .tree-node').each(function(){
                        var flg= $(this).children('.items').children('li').length>0
                        if (flg){
                            $(this).children('.name').children('.arrow').children().removeClass('hide')
                        }else{
                            $(this).children('.name').children('.arrow').children().addClass('hide')
                        }
                    })
                }
                resetTreeArrow()
            });
        }

        $("#modalDetailDepartmentItem").on('hide.bs.modal', function () {          
             if(hasChangeDepartment){
                 location.reload();
             }
        });
    </script>
@endpush