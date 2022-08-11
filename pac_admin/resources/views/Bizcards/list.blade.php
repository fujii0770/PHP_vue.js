<div ng-controller="ListController">
    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('filter','検索文字列', Request::get('filter', '') ,'text', false,
                    [ 'placeholder' =>'名前, 会社名, メールアドレス, 部署, 役職', 'id'=>'filter' ]) !!}
                </div>
                <div class="col-lg-6"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >公開範囲</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::BIZCARD_DISPLAY_TYPE, 'display_type', Request::get('display_type', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >状態</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::BIZCARD_DEL_FLG, 'del_flg_setting', Request::get('del_flg_setting', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                <input type="hidden" class="action" name="action" value="search" />
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        @if($bizcards)
            <div class="card mt-3">
                <div class="card-header">名刺一覧</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                    <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                        <select style="width: 100px" name="limit" onchange="javascript:document.adminForm.submit();" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option {{Request::get('limit') == '10' ? 'selected': ''}} value="10">10</option>
                                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                        </select>
                                    </label>
                                    @can(\App\Http\Utils\PermissionUtils::PERMISSION_BIZ_CARDS_DELETE)
                                        <button type="button" class="btn btn-danger" ng-disabled="numChecked == 0" ng-click="delete($event)"><i class="fas fa-trash-alt"></i> 削除</button>
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
                                <th scope="col">名刺画像</th>
                                <th scope="col">名前</th>
                                <th scope="col">会社名</th>
                                <th scope="col">電話番号</th>
                                <th scope="col">住所</th>
                                <th scope="col">メールアドレス</th>
                                <th scope="col">部署</th>
                                <th scope="col">役職</th>
                                <th scope="col">公開範囲</th>
                                <th scope="col">状態</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bizcards as $i => $bizcard)
                                <tr class="row-{{ $bizcard['bizcard_id'] }} row-edit" ng-class="{ edit: id == {{ $bizcard['bizcard_id'] }} }">
                                    <td class="title">
                                        <input type="checkbox" value="{{ $bizcard['bizcard_id'] }}" class="cid" onClick="isChecked(this.checked)" />
                                    </td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})"><img src="{{ $bizcard['bizcard'] }}" style="width: 100px;"></td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['name'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['company_name'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['phone_number'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['address'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['email'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['department'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ $bizcard['position'] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ \App\Http\Utils\AppUtils::BIZCARD_DISPLAY_TYPE[$bizcard['display_type']] }}</td>
                                    <td ng-click="editRecord({{ $bizcard['bizcard_id'] }})">{{ \App\Http\Utils\AppUtils::BIZCARD_DEL_FLG[$bizcard['del_flg']] }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $bizcards])
                </div>
                <input type="text" class="boxchecked" ng-model="numChecked" style="display: none;" />
            </div>
        @endif
    </form>
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $rootScope.search = {filter: '', display_type: '', del_flg_setting: ''};
                $scope.numChecked = 0;

                $scope.delete = function(event){
                    event.preventDefault();

                    let bizcard_ids = [];
                    for(var i = 0; i < $(".cid:checked").length; i++){
                        bizcard_ids.push($(".cid:checked")[i].value);
                    }

                    $rootScope.$emit("showMocalConfirm", {
                        title:'選択した名刺を削除します。よろしいですか？',
                        btnDanger:'削除',
                        callDanger: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_deletes, {bizcard_ids: bizcard_ids})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    location.reload();
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                }
                            });
                        }
                    });
                }

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditBizcard", {id: id});
                };
            });
        }
        $("#modalDisplaySetting").on('hide.bs.modal', function () {
            if(hasChange){
                location.reload();
            }
        });
    </script>
@endpush
