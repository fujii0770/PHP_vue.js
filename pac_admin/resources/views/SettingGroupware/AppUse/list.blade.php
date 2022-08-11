<div ng-controller="ListController" class="list-view">
    <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
        @csrf
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-lg-4 control-label">アプリ</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelectNonDefault($listapp, 'filter_app', Request::get('filter_app', ''),['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'text', false,
                    [ 'placeholder' =>'メールアドレス(部分一致)' ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('username','氏名',Request::get('username', ''),'text', false,
                    [ 'placeholder' =>'氏名(部分一致)' ]) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">部署</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">役職</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-lg-4 control-label">状態</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect($usestate, 'usestate', Request::get('usestate', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4"></div>
                <div class="col-lg-2 text-right">
                    <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                </div>
                <div class="col-lg-1"></div>
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        <div class="card mt-3">
            <div class="card-header">利用者一覧</div>
            <div class="card-body">
                <div class="table-head">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-4">
                                <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                    <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                        <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                        <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                        <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                    </select>
                                </label>
                            </div>
                            <div class="col-lg-8 text-right">
                                @canany([PermissionUtils::PERMISSION_APP_USE_SETTING_UPDATE])
                                @if (count($listapp)>0)
                                  <button class="btn btn-success m-1" ng-click="update($event)"><i class="far fa-save"></i>更新</button>
                                @endif
                                @endcanany
                            </div>
                        </div>
                    </div>
                </div>
                <span class="clear"></span>

                <table class="tablesaw-list tablesaw table-bordered adminlist mt-3" data-tablesaw-mode="swipe">
                    <thead>
                    <tr>
                        <th class="title sort" scope="col" data-tablesaw-priority="persist">
                            <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>
                        </th>
                        <th class="title sort" scope="col" data-tablesaw-priority="persist" style="width: 80px">
                            {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'state', $orderBy, $orderDir) !!}
                        </th>
                        <th scope="col" class="sort" style="width: 400px">
                            {!! \App\Http\Utils\CommonUtils::showSortColumn('メールアドレス', 'email', $orderBy, $orderDir) !!}
                        </th>
                        <th scope="col" class="sort" style="width: 400px">
                            {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'user_name', $orderBy, $orderDir) !!}
                        </th>
                        <th scope="col" class="sort" style="width: 100px">
                            {!! \App\Http\Utils\CommonUtils::showSortColumn('部署', 'adminDepartment', $orderBy, $orderDir) !!}
                        </th>
                        <th scope="col" class="sort" style="width: 100px">
                            {!! \App\Http\Utils\CommonUtils::showSortColumn('役職', 'position', $orderBy, $orderDir) !!}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($listuser)
                        @foreach ($listuser as $i => $item)
                            <tr class="row-{{ $item['id'] }} row-edit" ng-class="{ edit: id == {{ $item['id'] }} }" >
                                <td class="title">
                                    <input type="checkbox" value="{{ $item['id'] }}" ng-click="toogleCheck({{ $item['id'] }})"
                                           name="cids[]" class="cid" onClick="isChecked(this.checked)" <?= $item['enabled'] ? 'checked' : '' ?>/>
                                </td>
                                <td ng-show="true" class="title">{{ \App\Http\Utils\AppUtils::STATE_COMPANY[ $item['enabled']] }}</td><!-- STATE_COMPANY使えるからいいよね？ -->
                                <td ng-show="true" class="title">{{ $item['email'] }}</td>
                                <td ng-show="true" class="title">{{ $item['name'] }}</td>
                                <td ng-show="true" class="title">{{ $item['department'] }}</td>
                                <td ng-show="true" class="title">{{ $item['position'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <!-- Paginator使えればいいんだろうけどなぁ -->
                @if ($listuser)
                    @include('layouts.table_footer',['data' => $listuser])
                @endif
            </div>
        </div>
        <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
        <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
        <input type="hidden" name="page" value="1">
    </form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){

            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.update = function(event){
                    event.preventDefault();

                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'アプリ利用を更新しますか？',
                        btnSuccess:'はい',
                        callSuccess: function(){
                            var cids = [];
                            var cidsoff = [];

                            var hit = false;
                            //全てのチェックボックスを探索
                            for(var i =0; i < $(".cid").length; i++){
                                var hit = false;
                                //チェックされたチェックボックスを探索
                                for(var i2 =0; i2 < $(".cid:checked").length; i2++){
                                    //一致していたら1更新ルート
                                    if($(".cid")[i].value == $(".cid:checked")[i2].value){
                                        cids.push($(".cid")[i].value);
                                        hit = true;
                                        break;
                                    }
                                }
                                //不一致の場合0更新ルート
                                if (!hit){
                                    cidsoff.push($(".cid")[i].value);
                                }
                            }

                            $rootScope.$emit("showLoading");
                            $http.put(link_ajax_update, { select_app_id:document.getElementById("filter_app").value,cids: cids, cidoffs: cidsoff })
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
                $(document).ready(function() {
                    $('select[name="limit"]').change(function () {
                        document.adminForm.submit();
                    });
                });
            });
        }
    </script>
@endpush

