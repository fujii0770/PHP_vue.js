<div ng-controller="ListController">
        
    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'email', false, 
                    [ 'placeholder' =>'メールアドレス(部分一致)', 'id'=>'email' ]) !!}
                </div>                
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('name','氏名',Request::get('name', ''),'text', false, 
                    [ 'placeholder' =>'氏名(部分一致)', 'id'=>'name' ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('company_name','会社名',Request::get('company_name', ''),'text', false, 
                    [ 'placeholder' =>'会社名(部分一致)', 'id'=>'company_name' ]) !!}
                </div>                
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('position','役職',Request::get('position', ''),'text', false, 
                    [ 'placeholder' =>'役職名(部分一致)', 'id'=>'position' ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                @can([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_CREATE])
                    <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
                @endcan
                @canany([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_CREATE, PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_UPDATE])
                    <div class="btn btn-success  mb-1" ng-click="upload()"><i class="fas fa-upload" ></i> CSV取込</div>
                @endcanany
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        </div>
        <div class="message message-list mt-3"></div>
        @if($query)
            <div class="card mt-3">
                <div class="card-header">検索結果一覧</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <!--
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数</label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                   {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!} 
                                </div>
                                <div class="col-12 col-md-6 col-xl-10 mb-3 text-right">
                                -->
                                    <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                        {{--PAC_5-1978 共通アドレス帳に表示する項目数を変更する Start--}}
                                        <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                            <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                                <option {{Request::get('limit') == '10' ? 'selected': ''}} value="10">10</option>
                                                <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                                <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                            </select>
                                        </label>
                                        {{--PAC_5-1978 End--}}
                                        @if($sanitizing_flg == 0)                    
                                            {{--PAC_5-1978 共通アドレス帳のCSVを全件出力できるように修正する Start--}}
                                            <button type="button" class="btn btn-warning" ng-click="downloadCsv()"><i class="fas fa-download" ></i> CSV出力</button>
                                            {{--PAC_5-1978 End--}}
                                        @endif
                                    @can([PermissionUtils::PERMISSION_COMMON_ADDRESS_BOOK_DELETE])
                                        <button class="btn btn-danger" ng-click="delete($event)" ng-disabled="numChecked == 0"><i class="fas fa-minus-circle" ></i> 削除</button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="clear"></span>

                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                   <input type="checkbox" onClick="checkAll(this.checked)" />
                                </th>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'name', $orderBy, $orderDir) !!}
                                </th>                                     
                                <th scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('会社名', 'company_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('役職', 'position_name', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($query as $i => $address)
                                <tr class="row-{{ $address->id }} row-edit" ng-class="{ edit: id == {{ $address->id }} }">
                                    <td class="title">
                                        <input type="checkbox" value="{{ $address->id }}"
                                            class="cid" name="cid[]" onClick="isChecked(this.checked)" />
                                    </td>
                                    <td class="title" ng-click="editRecord({{ $address->id }})">{{ $address->email }}</td>
                                    <td ng-click="editRecord({{ $address->id }})">{{$address->name}}</td>
                                    <td ng-click="editRecord({{ $address->id }})">
                                        {{ $address->company_name}}
                                    </td>
                                    <td ng-click="editRecord({{ $address->id }})">
                                        {{ $address->position_name}} 
                                    </td>
                                </tr>
                            @endforeach
                            
                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $query])
                </div>
                <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                <input type="hidden" name="page" value="{{Request::get('page',1)}}">
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;" />
                
            </div>
        @endif
        
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                
                $rootScope.search = {email:"", name:"", company_name: "",position:"",status:""};
                $scope.numChecked = 0;

                $scope.addNew = function(){
                    $rootScope.$emit("openNewUser");
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditUser",{id:id});
                 };

                 $scope.upload = function(){ 
                    $("#modalImport").modal();
                    $rootScope.$emit("showModalImport");
                 };

                 $scope.download = function(){ 
                    $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'選択したアドレス帳データを出力します。実行しますか？', 
                            btnSuccess:'はい',
                            callSuccess: function(){
                                $(".action").val('export');
                                document.adminForm.submit();
                            }
                        });
                 };

                 $scope.downloadCsv = function(){ 
                    $rootScope.$emit("showMocalConfirm", 
                        {
                            title:'選択したアドレス帳データを出力します。実行しますか？', 
                            btnSuccess:'はい',
                            callSuccess: function(){
                                // PAC_5-1978 共通アドレス帳のCSVを全件出力できるように修正する Start
                                var data = {
                                    cids: [],
                                    email: "{!! Request::get('email', '') !!}",
                                    name: "{!! Request::get('name', '') !!}",
                                    company_name: "{!! Request::get('company_name', '') !!}",
                                    position: "{!! Request::get('position', '') !!}",
                                    orderBy: "{!! Request::get('orderBy', '') !!}",
                                    orderDir: "{!! Request::get('orderDir', '') !!}",
                                };
                                for(var i =0; i < $(".cid:checked").length; i++){
                                    data.cids.push($(".cid:checked")[i].value);
                                }
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_address_common, data)
                                // PAC_5-1978 End
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

                $scope.delete = function(event){ 
                    event.preventDefault();
                    var cids = [];
                    for(var i =0; i < $(".cid:checked").length; i++){
                        cids.push($(".cid:checked")[i].value);
                    }
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択したアドレス帳データを削除します。実行しますか？', 
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_delete_select, { cids: cids})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    location.reload();
                                }                        
                            });
                        }
                    });

                 };

                
            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
            $(".adminlist tr.edit").removeClass('edit');
             if(hasChange){
                 location.reload();
             }
        });
        // PAC_5-1978 共通アドレス帳に表示する項目数を変更する Start
        $(document).ready(function() {
            $('select[name="limit"]').change(function () {
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });
        });
        // PAC_5-1978 End
    </script>
@endpush