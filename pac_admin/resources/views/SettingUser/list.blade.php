<div ng-controller="ListController">

    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('email','メールアドレス',Request::get('email', ''),'text', false,
                    [ 'placeholder' =>'メールアドレス（部分一致）', 'id'=>'email' ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('name','氏名',Request::get('name', ''),'text', false,
                    [ 'placeholder' =>'氏名（部分一致）', 'id'=>'name' ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >部署</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\DepartmentUtils::buildDepartmentSelect($listDepartmentTree, 'department', Request::get('department', ''),'',['class'=> 'form-control select2']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >役職</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect($listPosition, 'position', Request::get('position', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4 form-group">
                    <div class="row">
                        <label class="col-md-4 control-label" >状態</label>
                        <div class="col-md-8">
                            {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::STATE_USER_LABEL , 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4"></div>
                <div class="col-lg-2"></div>
            </div>
            <input type="hidden" name="departmentChild" value="true">
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_CREATE])
                    <div class="btn btn-success mb-1" ng-click="addNew()"><i class="fas fa-plus-circle" ></i> 登録</div>
                    <div class="btn btn-success mb-1" ng-click="upload(0)"><i class="fas fa-upload" ></i> CSV取込</div>
                @endcanany
                @if(empty($users))
                    <input type="hidden" name="limit" value="20" />
                @endif            
                <div class="btn btn-warning mb-1" ng-click="downloadCsv(0)"><i class="fas fa-download" ></i> CSV出力</div>
                <input type="hidden" class="action" name="action" value="search" />
            </div>
            <div class="text-right" style="display:{{$company->without_email_flg ? 'block':'none' }}">
                @canany([PermissionUtils::PERMISSION_USER_SETTINGS_CREATE])
                    <div class="btn btn-success mb-1" ng-click="upload(1)"><i class="fas fa-upload" ></i> <i class="fas fa-envelope" ></i> 無ユーザーCSV取込</div>
                @endcanany
                <div class="btn btn-warning mb-1" ng-click="downloadCsv(1)"><i class="fas fa-download" ></i> <i class="fas fa-envelope" ></i> 無ユーザーCSV出力</div>
            </div>
        </div>

        <div class="message message-list mt-3"></div>
        @if($users)
            <div class="card mt-3">
                <div class="card-header">利用者一覧</div>
                <div class="card-body">
                    <div class="table-head">
                        <div class="form-group">
                            <div class="row">
                                <!--PAC_5-350-->
                                <!--
                                <label class="col-6 col-md-2 col-xl-1 control-label text-right mb-3" >表示件数</label>
                                <div class="col-6 col-md-4 col-xl-1 mb-3">
                                    {!! Form::select("limit", config('app.page_list_limit'), $limit, ['class' => 'form-control', 'onchange' => 'javascript:document.adminForm.submit();']) !!}
                                </div>
                                -->
                                <!--<div class="col-12 col-md-6 col-xl-10 mb-3 text-right">-->
                                <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                    <label class="d-flex" style="float:left" ><span style="line-height: 27px">表示件数：</span>
                                        <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                        </select>
                                    </label>
                                    @canany([PermissionUtils::PERMISSION_USER_SETTINGS_DELETE])
                                        <button type="button" class="btn btn-danger" ng-disabled="numChecked==0" ng-click="delete($event)"><i class="fas fa-trash-alt"></i> 削除</button>
                                    @endcanany
                                    @if($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO)
                                        <button class="btn btn-warning" ng-click="sendEmail($event,true)"  ng-disabled="numChecked == 0">
                                            <i class="fas fa-envelope" ></i>ログインURL送信
                                        </button>
                                    @endif
                                    @if($company->login_type != \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO ||($company->login_type == \App\Http\Utils\AppUtils::LOGIN_TYPE_SSO  && $company->use_mobile_app_flg == \App\Http\Utils\AppUtils::FLG_ENABLE))
                                            @canany([\App\Http\Utils\PermissionUtils::PERMISSION_USER_SETTINGS_CREATE,\App\Http\Utils\PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE])    
                                            <button class="btn btn-warning" ng-click="sendEmail($event,false)"  ng-disabled="numChecked == 0">
                                                <i class="fas fa-envelope" ></i>初期パスワード設定
                                            </button>
                                            @endcanany
                                            @if ($company->without_email_flg == 1)
                                                <button type="button" class="btn btn-warning" ng-click="showTable()" ng-disabled="numChecked == 0">
                                                    <i class="fas fa-key"></i> パスワード設定コード
                                                </button>
                                            @endif
                                    @endif
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
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名', 'given_name', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col">部署</th>
                                <th scope="col">役職</th>
                                {{--PAC_5-2098 Start--}}
                                @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                <th scope="col">部署2</th>
                                <th scope="col">役職2</th>
                                <th scope="col">部署3</th>
                                <th scope="col">役職3</th>
                                @endif
                                {{--PAC_5-2098 End--}}
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('氏名印', 'stampName', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('日付印', 'stampDate', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('共通印', 'stampCommon', $orderBy, $orderDir) !!}
                                </th>
                                @if(isset($company->convenient_flg) && $company->convenient_flg == 1)
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('便利印', 'stampConvenient', $orderBy, $orderDir) !!}
                                </th>
                                @endif
                                <th scope="col"  class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('状態', 'state_flg', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ﾊﾟｽﾜｰﾄﾞ', 'password', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $i => $user)
                                @if($user->state_flg == 1)
                                    <tr class="row-{{ $user['id'] }} row-edit" ng-class="{ edit: id == {{ $user['id'] }} }">
                                @else
                                    <tr class="row-{{ $user['id'] }} row-edit row-disabled" ng-class="{ edit: id == {{ $user['id'] }} }">
                                @endif
                                    <td class="title">
                                        <input type="checkbox" value="{{ $user['id'] }}"  class="cid" onClick="isChecked(this.checked)" />
                                    </td>
                                    <td class="title" ng-click="editRecord({{ $user['id'] }})">{{ $user->email }}</td>
                                    <td ng-click="editRecord({{ $user['id'] }})">{{ $user->family_name . " ".$user->given_name }}</td>
                                    <td ng-click="editRecord({{ $user['id'] }})">
                                        <!-- 部署の情報を取得する-->
                                        @isset($listDepartmentDetail[$user->info->mst_department_id]['text'])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="editRecord({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id])
                                            {{ $listPosition[$user->info->mst_position_id]['text'] }}
                                        @endisset
                                    </td>
                                    {{--PAC_5-2098 Start--}}
                                    @if(isset($company->multiple_department_position_flg) && $company->multiple_department_position_flg == 1)
                                    {{--PAC_5-1599 追加部署と役職 Start--}}
                                    <td ng-click="editRecord({{ $user['id'] }})">
                                        @isset($listDepartmentDetail[$user->info->mst_department_id_1]['text'])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id_1]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="editRecord({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id_1])
                                            {{ $listPosition[$user->info->mst_position_id_1]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="editRecord({{ $user['id'] }})">
                                        @isset($listDepartmentDetail[$user->info->mst_department_id_2]['text'])
                                            {{ $listDepartmentDetail[$user->info->mst_department_id_2]['text'] }}
                                        @endisset
                                    </td>
                                    <td ng-click="editRecord({{ $user['id'] }})">
                                        @isset($listPosition[$user->info->mst_position_id_2])
                                            {{ $listPosition[$user->info->mst_position_id_2]['text'] }}
                                        @endisset
                                    </td>
                                    {{--PAC_5-1599 End--}}
                                    @endif
                                    {{--PAC_5-2098 End--}}
                                    <td ng-click="editRecord({{ $user['id'] }})">{{ $user->stampName }}</td>
                                    <td ng-click="editRecord({{ $user['id'] }})">{{ $user->stampDate }}</td>
                                    <td ng-click="editRecord({{ $user['id'] }})">{{ $user->stampCommon }}</td>
                                    @if(isset($company->convenient_flg) && $company->convenient_flg == 1)
                                    <td ng-click="editRecord({{ $user['id'] }})">{{ $user->stampConvenient }}</td>
                                    @endif
                                <td ng-click="editRecord({{ $user['id'] }})">{{ \App\Http\Utils\AppUtils::STATE_USER[$user->state_flg] }}</td>
                                    <td ng-click="editRecord({{ $user['id'] }})">{{ $user->password==""?"未設定":"設定済" }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    @include('layouts.table_footer',['data' => $users])
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

                $rootScope.search = {email:"", name:"", department: "",position:"",status:"",departmentChild:true};
                $scope.numChecked = 0;
                // PAC_5-2163  利用者情報更新画面で初期パスワード設定を送るときメールが無効だったらモーダル表示させる
                $scope.checkUsersEmailEnable = 0;
                $scope.checkUsersStatusEnable = 0;
                $scope.checkUsersHasStamp = 0;
                $scope.objUsersStatus = null;
                $scope.userCount = {!! json_encode($users_count) !!};
                $scope.company = {!! json_encode($company) !!};
                $rootScope.without_email_import_flg = 0;

                $scope.addNew = function(){
                    if ($scope.company.form_user_flg && $scope.userCount >= 5){
                        $rootScope.$emit("showMocalAlert",
                            {
                                size:'md',
                                title:"警告",
                                message:'5人以上のユーザの登録は行えません。',
                            });
                    }else {
                        $rootScope.$emit("openNewUser");
                    }
                };

                $scope.editRecord = function(id){
                    $rootScope.$emit("openEditUser",{id:id});
                 };

                $scope.upload = function (without_email_flg) {
                    $rootScope.without_email_import_flg = without_email_flg;
                    $("#modalImport").modal();
                    $rootScope.$emit("showModalImport");
                };

                $scope.downloadCsv = function(without_email_flg){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'表示されている利用者データを出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_csv, {without_email_flg:without_email_flg })
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
                            title:'選択した利用者を削除します。よろしいですか？',
                            btnDanger:'削除',
                            callDanger: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_deletes, { cids: cids})
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

                };

                 $scope.sendEmail = function(event,sendLoginUrl){
                    var send_mail = link_reset;
                    var message = '選択した利用者に初期パスワードの通知メールを送信します。実行しますか？';
                    if(sendLoginUrl){
                        send_mail = link_send_login_url;
                        message = '選択した利用者にログインURLを送信します。実行しますか？';
                    }
                    event.preventDefault();

                    var cids = [];
                    for(var i =0; i < $(".cid:checked").length; i++){
                        cids.push($(".cid:checked")[i].value);
                    }
                     $rootScope.$emit("showLoading");
                     // PAC_5-2163  利用者情報更新画面で初期パスワードの通知を送るときメールが無効だったらモーダル表示させる
                     if($scope.objUsersStatus == null){

                         $http.post(link_checkUserEmailOrStampOrStatus, { uids: cids})
                             .then(function(checkEvent) {
                                 $rootScope.$emit("hideLoading");
                                 if(checkEvent.data.status == false){
                                     $(".message-list").append(showMessages(checkEvent.data.message, 'danger', 10000));
                                     return ;
                                 }
                                 $scope.objUsersStatus = checkEvent.data.data;
                                 $scope.sendEmail(event,sendLoginUrl);
                             });
                         return ;
                     }

                     if($scope.checkUsersEmailEnable == 0 && $scope.objUsersStatus.isEmailInvalid.length > 0){
                         $rootScope.$emit("hideLoading");
                         $rootScope.$emit("showMocalConfirm",
                             {
                                 title: 'メールの設定を有効にしないと初期パスワードの通知メールが届きません。<br />選択された利用者のメールの設定を有効にしますか？',
                                 btnSuccess:'はい',
                                 size:'lg',
                                 callSuccess: function(){
                                     $http.post(link_setUsersEmailOrStampOrStatus, { uids: cids,type:1})
                                         .then(function(setUserEvent) {
                                             if(setUserEvent.data.status == false){
                                                 $(".message-list").append(showMessages(setUserEvent.data.message, 'danger', 10000));
                                                 return ;
                                             }
                                             $scope.checkUsersEmailEnable = 1;
                                             $scope.objUsersStatus.isEmailInvalid = [];
                                             hasChange = true;
                                             $scope.sendEmail(event,sendLoginUrl);
                                         });
                                 },
                                 callCancel:function(){
                                     $scope.objUsersStatus=null
                                 }
                             });

                         return ;
                     }

                     var applyStamp = false;
                     // 氏名印0件
                     if($scope.objUsersStatus && $scope.objUsersStatus.isNoStampMaster.length > 0){
                         //デフォルト印OFF以外の場合
                         if($scope.company.default_stamp_flg == 0){
                             applyStamp = true;
                         }else{
                             // デフォルト印ONの場合、共通印のみだけでも登録できる
                             // また、デフォルト印OFF以外の場合、部署名入り日付印も日付印と見なし、部署名入り日付印だけでもいい
                             if($scope.objUsersStatus.isNoStampTwo.length > 0){
                                 applyStamp = true;
                             }
                         }
                     }

                     if(applyStamp){
                         $rootScope.$emit("hideLoading");
                         $scope.checkUsersEmailEnable = 0;
                         $scope.checkUsersStatusEnable = 0;
                         $scope.checkUsersHasStamp = 0;
                         $scope.objUsersStatus = null;
                         hasChange = true;
                         $rootScope.$emit("showMocalAlert",
                             {
                                 size:'md',
                                 title:"警告",
                                 message:'初期パスワード通知するためには、印面を登録する必要があります。<br />選択された利用者の印面を登録してください。',
                             });
                         return ;
                     }

                     if($scope.objUsersStatus.isStatusInvalid.length > 0){
                         $rootScope.$emit("hideLoading");
                         $rootScope.$emit("showMocalConfirm",
                             {
                                 title: '初期パスワード通知するためには状態を有効にする必要があります。<br />選択された利用者の状態の設定を有効にしますか？',
                                 btnSuccess:'はい',
                                 size:'lg',
                                 callSuccess: function(){
                                     $http.post(link_setUsersEmailOrStampOrStatus, { uids: cids,type:2})
                                         .then(function(setUserEvent) {
                                             if(setUserEvent.data.status == false){
                                                 if(setUserEvent.data.convenient_stamp_is_over == 1){
                                                     $rootScope.$emit("showMocalAlert",
                                                         {
                                                             size:'md',
                                                             title:"警告",
                                                             message:setUserEvent.data.message[0],
                                                         });
                                                     return ;
                                                 }
                                                 $(".message-list").append(showMessages(setUserEvent.data.message, 'danger', 10000));
                                                 return ;
                                             }
                                             hasChange = true;
                                             $scope.objUsersStatus.isStatusInvalid = [];
                                             $scope.sendEmail(event,sendLoginUrl);
                                         });
                                 },
                                 callCancel:function (){
                                     $scope.objUsersStatus=null
                                 }
                             });
                         hasChange = true;
                         return ;
                     }
                     $rootScope.$emit("hideLoading");
                     $rootScope.$emit("showMocalConfirm",
                         {
                             title: message,
                             btnSuccess:'はい',
                             size:'lg',
                             callSuccess: function(){
                                 $http.post(send_mail, { cids: cids, user_type: 'option_user'})
                                     .then(function(alertEvent) {
                                         $rootScope.$emit("hideLoading");
                                         hasChange = true;
                                         if(alertEvent.data.status == false){
                                             $(".message-list").append(showMessages(alertEvent.data.message, 'danger', 10000));
                                         }else{
                                             $(".message-list").append(showMessages(alertEvent.data.message, 'success', 10000));
                                         }
                                         setTimeout ('location.reload()',2000);
                                     });
                             },
                             callCancel: function(){
                                 setTimeout ('location.reload()',1000);
                             }
                         });
                 };
                
                $scope.showTable = function(event){
                    // 検索内容に準拠して表示
                    var cids = [];
                    for(var i =0; i < $(".cid:checked").length; i++){
                        cids.push($(".cid:checked")[i].value);
                    }
                    $rootScope.$emit("showLoading");
                    $http.post(link_show_password_list, { cids: cids }).then(
                        function(event) {
                            $rootScope.$emit("hideLoading");
                            $rootScope.$emit("showModalPasswordCodeTable",{
                                title:'パスワード設定コード一覧',
                                message:'パスワード設定コードを発行しました。',
                                btnClose:'閉じる',
                                rows:event.data.user_list,
                                callClose: function(){
                                    $rootScope.$emit("showLoading");
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                }
                            });
                        });
                };
            })
        }else{
            throw new Error("Something error init Angular.");
        }

        $(document).ready(function() {
            $('select[name="limit"]').change(function () {
                var value = $(this).val();
                $('input[name="page"]').val('1');
                document.adminForm.submit();
            });
            $('form[name="adminForm"]').submit(function(e){
                $('input[name="page"]').val('1');
            });
            $('.select2').select2({
                placeholder: '',
                allowClear: true,
                "language": {
                    "noResults": function(){
                        return "データがありません";
                    }
                }
            });
        });

        $("#modalDetailItem").on('hide.bs.modal', function () {
            $(".adminlist tr.edit").removeClass('edit');
             if(hasChange){
                 location.reload();
             }
        });
    </script>
@endpush

@push('styles_after')
    <style>
        .select2-container .select2-selection{
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow{ height: 36px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered{     line-height: 24px; }
    </style>
@endpush
