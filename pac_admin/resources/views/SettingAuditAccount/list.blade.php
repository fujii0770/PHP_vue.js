<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET">

        <div class="message message-list mt-3"></div>
        @if($audits)
            <div class="card mt-3">
                <div class="card-header">監査用アカウント一覧</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数</label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                    {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                                </div>
                                <div class="col-12 col-md-6 col-xl-10 mb-3 text-right">
                                    <div class="text-right">
                                        @canany([PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_CREATE])
                                        <div class="btn btn-success  mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
                                        @endcanany
                                        @canany([PermissionUtils::PERMISSION_AUDIT_ACCOUNT_SETTING_DELETE])
                                        <button class="btn btn-danger mb-1" ng-click="delete($event)" ng-disabled="numChecked == 0"><i class="fas fa-trash" ></i> 削除</button>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <span class="clear"></span>
                    <table class="tablesaw-list tablesaw table-bordered adminlist margin-top-5" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th class="title" scope="col" data-tablesaw-priority="persist">
                                    <input type="checkbox" onClick="checkAll(this.checked)" />
                                </th>
                                <th scope="col" data-tablesaw-priority="persist">メールアドレス</th>
                                <th scope="col">名称</th>
                                <th scope="col">有効期限</th>
                                <th scope="col">状態</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($audits as $i => $audit)
                        <tr  class="row-{{ $audit->id }} row-edit" scope="row" ng-class="{ edit: id == {{ $audit->id }} }">
                            <td class="title">
                                <input type="checkbox" value="{{ $audit->id }}"
                                       class="cid" onClick="isChecked(this.checked)" />
                            </td>
                            <td ng-click="editRecord({{ $audit->id }})">{{ $audit->email }}</td>
                            <td ng-click="editRecord({{ $audit->id }})">{{ $audit->account_name }}</td>
                            <td ng-click="editRecord({{ $audit->id }})">{{ $audit->expiration_date }}</td>
                            <td ng-click="editRecord({{ $audit->id }})">{{ \App\Http\Utils\AppUtils::STATE_AUDIT[$audit->state_flg] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $audits])
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
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http) {
                $scope.numChecked = 0;
                $scope.addNew = function(){
                    $rootScope.$emit("openNewAuditUser");
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditAuditUser",{id:id});
                };
                $scope.delete = function(event){
                    event.preventDefault();
                    let cids = [];
                    for(let i =0; i < $(".cid:checked").length; i++){
                        cids.push($(".cid:checked")[i].value);
                    }
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択したアカウントを削除します。よろしいですか？',
                            btnDanger:'はい',
                            callDanger: function(){
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
    </script>
@endpush
