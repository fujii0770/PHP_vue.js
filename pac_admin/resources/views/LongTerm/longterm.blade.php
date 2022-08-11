@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('circulars-long-term') }}";
    </script>
@endpush

@section('content')

    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
        @csrf
        @if($company->long_term_folder_flg)
        <div class="row">
            <div class="folder_card col-lg-3 {!! Session::get('isNavMenuActive') == 1 ? 'active-folder' : '' !!}" ng-show="company.long_term_folder_flg">
                <ul class="items tree mt-3" id="sortable_depart" style="overflow-x: auto;white-space: nowrap;height: 100%;">
                    <li class="tree-node parent">
                        <div class="name " data-id="0" data-longTermFolder="{{ $company->company_name }}" data-parent="NULL" ng-class="{selected: selectedID == 0}" ng-click="selectRow(0)">
                            <span class="arrow">
                                <i class="fas fa-caret-down icon icon-down"></i> <i class="fas fa-caret-right icon icon-right"></i>
                            </span>
                            <i class="far fa-folder"></i>
                            {{ $company->company_name }}
                        </div>
                        <ul class="items">
                            @foreach ($itemsFolder as $item)
                                @include('Circulars.folder_tree_node',['itemFolder' => $item])
                            @endforeach
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="main_card ">
        @endif
            <div class="form-search form-horizontal">
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        {!! \App\Http\Utils\CommonUtils::showFormField('documentName','件名',Request::get('documentName', ''),'text', false,
                        [ 'placeholder' =>'件名（部分一致）' ]) !!}
                    </div>
                    <div class="col-lg-6">
                        <div class="row form-group">
                            <label for="document_id" class="col-md-3 control-label">キーワード</label>
                            <div class="col-md-5">
                                <input name="keyword" value="{{ Request::get('keyword', '') }}" class="form-control" id="keyword">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="row" ng-if="isShowLongTermindex=='1'">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-8">
                        <div class="row form-group">
                            <label class="col-md-2 control-label" >金額</label>
                            <div class="col-md-3">
                                <input type="number" name="frommoney" value="{{ Request::get('frommoney', '') }}" class="form-control" id="frommoney">
                            </div>
                            <label for="name" class="col-md-1 control-label">~</label>
                            <div class="col-md-3">
                                <input type="number" name="tomoney" value="{{ Request::get('tomoney', '') }}" class="form-control" id="tomoney">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" ng-if="isShowLongTermindex=='1'">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-2 control-label">取引年月日</label>
                                <div class="col-md-3">
                                    <input type="Date" name="fromdate" value="{{ Request::get('fromdate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="fromdate">
                                </div>
                                <label for="name" class="col-md-1 control-label">~</label>
                                <div class="col-md-3">
                                    <input type="date" name="todate" value="{{ Request::get('todate', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="todate">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" ng-if="isShowLongTermindex=='1'">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <div class="row">
                                <label for="name" class="col-md-2 control-label">取引先</label>
                                <div class="col-md-3">
                                    <input type="text" name="customer" value="{{ Request::get('customer', '') }}" class="form-control" id="customer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    @foreach ($indexes as $i => $indexe)
                        <div class="row" id="row_add{{ $indexe['index'] }}">
                            <div class="col-lg-2">
                            </div>
                            <div class="col-lg-10">
                                <div class="row form-group">
                                    <div class="col-lg-3">
                                        <select name="longIndex{{ $indexe['index'] }}" class="form-control" id="longIndex{{ $indexe['index'] }}" onchange="showtovalue(this)">
                                            @foreach ($longTermIndexall as $j => $longTerm)
                                                @if($longTerm['id'] == $indexe['id'])
                                                    <option selected="selected" value="{{ $longTerm['id'] }}" id="{{ $longTerm['type'] }}">{{ $longTerm['value'] }}</option>
                                                @else
                                                    <option value="{{ $longTerm['id'] }}" id="{{ $longTerm['type'] }}">{{ $longTerm['value'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input name="fromkeyword{{ $indexe['index'] }}" value="{{ $indexe['fromvalue'] }}" class="form-control" id="fromkeyword{{ $indexe['index'] }}">
                                    </div>
                                    <label for="name" class="col-md-1 control-label" id="tilde{{ $indexe['index'] }}" style="display: {{ $indexe['display'] }}">~</label>
                                    <div class="col-md-3" id="tovalue{{ $indexe['index'] }}" style="display: {{ $indexe['display'] }}">
                                        <input name="tokeyword{{ $indexe['index'] }}" value="{{ $indexe['tovalue'] }}" class="form-control" id="tokeyword{{ $indexe['index'] }}">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="btn btn-danger " onclick="deleteIndex(this.id)" id="delete_button{{ $indexe['index'] }}"> x</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                <div class="row" id="row_add" ng-if="isShowLongTermindex=='1'">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-10">
                        <div class="row form-group">
                            <div class="btn btn-success " ng-click="addIndex()">+</div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-2 text-right">
                        <button class="btn btn-primary mb-1" type="submit" ng-click="search()" ng-disabled="company.long_term_folder_flg && selectedID == null" ><i class="fas fa-search"></i> 検索</button>
                    </div>
                    <div class="col-lg-1"></div>
                </div>

                <div class="message message-list mt-3"></div>
                <div class="card mt-3">
                    <div class="card-header">長期保管一覧</div>

                    <div class="card-body">
                        <div class="table-head">
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-lg-6 col-md-2 "  style="float:left" >
                                        <span style="line-height: 27px">表示件数：</span>
                                        <select style="width: 100px" name="limit" aria-controls="dtBasicExample" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option {{Request::get('limit') == '20' ? 'selected': ''}} value="20">20</option>
                                            <option {{Request::get('limit') == '50' ? 'selected': ''}} value="50">50</option>
                                            <option {{Request::get('limit') == '100' ? 'selected': ''}} value="100">100</option>
                                        </select>
                                    </label>
                                    <div class="col-lg-6 col-md-2 text-right">
                                        <span class="dropdown">
                                                    <button type="button" class="btn btn-warning mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ng-disabled="selected.length==0" ><i class="fas fa-download"></i> ダウンロード予約</button>
                                                    <div class="dropdown-menu action dropdown-menu-right"
                                                         ng-hide="selected.length==0" style="width: 200px">
                                                        <span class="dropdown-menu-arrow"></span>
                                                        <span style="padding: 10px;font-size: 14px; width: 80%" ng-if="!checkFlg">
                                                            <input id="check_add_stamp_history1" style="margin-right: 5px"
                                                                   class="mb-2 mt-2" type="checkbox"
                                                                   ng-model="check_add_stamp_history"
                                                                   ng-change="changeHistory()"
                                                            />
                                                            <label for="check_add_stamp_history1">回覧履歴を付ける</label>
                                                        </span>
                                                        <button type="button" class="btn btn-warning btn-block"
                                                                style="width: 90%; margin: auto;"
                                                                ng-click="download()"><i class="fas fa-download"></i> ダウンロード予約</button>
                                                    </div>
                                                </span>
                                        <button type="button" class="btn btn-primary mb-1" ng-disabled="selected.length==0" ng-if="company.long_term_folder_flg" ng-click="remove()">移動</button>
                                        <button type="button" class="btn btn-danger mb-1" ng-disabled="selected.length==0" ng-click="delete()"><i class="fas fa-trash-alt"></i> 削除</button>
                                        <button type="button" class="btn btn-primary mb-1" ng-disabled="selected.length==0" ng-if="isShowAutomaticUpdate=='1'" ng-click="automaticUpdateClick('1')"> 自動更新ON</button>
                                        <button type="button" class="btn btn-primary mb-1" ng-disabled="selected.length==0" ng-if="isShowAutomaticUpdate=='1'" ng-click="automaticUpdateClick('0')"> 自動更新OFF</button>
                                        <button type="button" class="btn btn-success mb-1"   ng-click="saveLongTerm()">  新規登録 </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="clear"></span>

                        <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                            <thead>
                            <tr>
                                <th class="title sort" scope="col" data-tablesaw-priority="persist">
                                    <input type="checkbox" onClick="checkAll(this.checked)" ng-model="selected_all" ng-change="toogleCheckAll()" />
                                </th>
                                <th scope="col" class="sort" style="width: 600px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('件名', 'LTD.title', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('サイズ', 'LTD.file_size', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('保管', 'LTD.upload_status', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('保存日時', 'LTD.create_at', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px" ng-if="isShowDate">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('タイムスタンプ付与日時', 'LTD.add_timestamp_automatic_date', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px" ng-if="isShowDate">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('タイムスタンプ自動更新', 'LTD.timestamp_automatic_flg', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 300px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ステータス', 'LTD.is_del', $orderBy, $orderDir) !!}
                                </th>
                            </tr>
                            </thead>
                                <tbody>
                                @foreach ($itemsCircular as $i => $item)
                                    <tr class="">
                                        <td class="title">
                                            <input type="checkbox" value="{{ $item->id }}" ng-model="{{'selected_id'.$item->id}}" ng-click="toogleCheck({{ $item->id }})"
                                                   name="cids[]" id="{{'selected_id'.$item->id}}" class="cid" onClick="isChecked(this.checked)" data-status="{{$item->upload_status}}" />
                                        </td>
                                        <td class="title" ng-click="showDetail({{ $item->id }})">{{ $item->title }}</td>
                                        <td ng-click="showDetail({{ $item->id }})">{{ round(($item->file_size)/(1024*1024), 2) }}MB</td>
                                        <td ng-click="showDetail({{ $item->id }})">{{ $item->upload_status==1?'外部':'完了' }}</td>
                                        <td ng-click="showDetail({{ $item->id }})">{{ date("Y/m/d H:i", strtotime($item->create_at)) }}</td>
                                        <td ng-if="isShowDate" ng-click="showDetail({{ $item->id }})">{{ $item->add_timestamp_automatic_date ? date("Y/m/d H:i", strtotime($item->add_timestamp_automatic_date)) : 'なし' }}</td>
                                        <td ng-if="isShowDate" ng-click="showDetail({{ $item->id }})">{{ $item->timestamp_automatic_flg == 1 ? "ON" : "OFF" }}</td>
                                        @if($item->is_del == 1)
                                        <td ng-click="showDetail({{ $item->id }})" data-html="true" data-toggle="tooltip" title="利用者側で文書が削除されています。<br>完全に文書を削除する場合には管理者側で文書を削除してください。<br>※管理者側で文書を削除した場合、文書を復元することができません。">{{ $item->is_del == 1 ? "削除" : "保存中" }}</td>
                                        @endif
                                        @if($item->is_del == 0)
                                        <td ng-click="showDetail({{ $item->id }})">{{ $item->is_del == 1 ? "削除" : "保存中" }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                        </table>
                        @include('layouts.table_footer',['data' => $itemsCircular])
                    </div>

                    <% boxchecked %>
                    <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
                    <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="action" value="">
                    <input type="hidden" name="automaticOnFlg" value="0">
                    <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
                    <input type="hidden" name="finishedDateHidden" value="">
                    <input type="hidden" name="finishedMonthHidden" id="finishedMonthHidden"  />
                    <input type="hidden" name="folderId" ng-value="selectedID">
                </div>
            </div>
            {{-- PAC_5-2285 --}}
            <div class="card mt-3" ng-if="showDetailId">
                <div class="card-header">詳細内容表示エリア</div>
                <div class="card-body">
                    <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                        <thead>
                        <tr>
                            <th scope="col" class="w-25">項目</th>
                            <th scope="col" class="w-75">内容</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="">
                            <td>プレビュー</td>
                            <td>
                                <div ng-if="showLongDetail.first_page_data" class="thumb-img" style="height: 60%; width: 60%;">
                                    <img style="max-height: 50%;max-width: 50%;" ng-src="data:image/jpeg;base64, <% showLongDetail.first_page_data %>" class="thumb-img" />
                                </div>
                                <div ng-if="!showLongDetail.first_page_data">プレビューするレコードを選択してください</div>
                            </td>
                        </tr>
                        <tr class="">
                            <td>件名</td>
                            <td><% showLongDetail.title %>
                                <span class="dropdown">
                                    <button type="button" class="btn btn-warning mb-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ><i class="fas fa-download"></i> ダウンロード予約</button>
                                    <div class="dropdown-menu action dropdown-menu-right" style="width: 200px">
                                        <span class="dropdown-menu-arrow"></span>
                                        <span style="padding: 10px;font-size: 14px; width: 80%" ng-if="!showLongDetail.upload_status">
                                            <input id="check_add_stamp_history" style="margin-right: 5px"
                                                   class="mb-2 mt-2" type="checkbox"
                                                   ng-model="check_add_stamp_history"
                                                   ng-click="changeHistory()"/>
                                            <label for="check_add_stamp_history">回覧履歴を付ける</label>
                                        </span>
                                        <button type="button" class="btn btn-warning btn-block"
                                                style="width: 90%; margin: auto;"
                                                ng-click="download('detail')"><i class="fas fa-download"></i> ダウンロード予約</button>
                                    </div>
                                </span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>ファイル名</td>
                            <td><% showLongDetail.file_name %></td>
                        </tr>
                        <tr class="">
                            <td>サイズ</td>
                            <td><% showLongDetail.file_size_mb %>MB</td>
                        </tr>
                        <tr class="">
                            <td>差出人</td>
                            <td><% showLongDetail.sender_emails %></td>
                        </tr>
                        <tr class="">
                            <td>宛先</td>
                            <td id="destination_emails"></td>
                        </tr>
                        <tr class="">
                            <td>申請日</td>
                            <td><% showLongDetail.upload_status?'-': showLongDetail.request_at %></td>
                        </tr>
                        <tr class="">
                            <td>承認完了日</td>
                            <td ><% showLongDetail.upload_status?'-': showLongDetail.completed_at %></td>
                        </tr>
                        <tr class="">
                            <td>キーワード</td>
                            <td>
                                <textarea placeholder="コメントをつけて送信できます。" rows="2" ng-model="showLongDetail.keyword"></textarea>
                                <p ng-if="checkKeywordsLenFlg" style="color:red">入力できる文字数は200文字が最大です。</p>
                            </td>
                        </tr>
                        <tr class="" ng-if="!showLongDetail.upload_status">
                            <td>添付ファイル情報</td>
                            <td>
                                <div id="circular_attachment_name_string" style="word-break: break-all"></div>
                                <div ng-if="checkCircularAttachment">
                                    <button type="button" class="btn btn-warning" ng-click="downloadAttachment()"><i class="fas fa-download"></i> ダウンロード</button>
                                </div>
                            </td>
                        </tr>
                        <tr ng-if="longTermStorageOptionFlg === 1">
                            <td>インデックス</td>
                            <td class="info">
                                <div ng-repeat="(id,item) in showLongDetail.circular_index" >
                                    <input type="text" disabled="disabled" style="text-align: left" ng-model="item.index_name"></input>
                                    <input type="text" disabled="disabled" style="text-align: left" ng-model="item.value"></input>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="col-lg-12 col-md-2 text-right">
                        <button type="button" class="btn btn-success" ng-click="onUpdateDocument()"><i class="fas fa-save"></i> 更新</button>
                        <button type="button" class="btn btn-danger" ng-click="onSingleDeleteDocumentClick()"><i class="fas fa-trash-alt"></i> 削除</button>
                    </div>
                </div>
            </div>
            @if($company->long_term_folder_flg)
            </div>
        </div>
        @endif
        </form>
    </div>
    @include('Circulars.remove_folder_tree_node')
@endsection

@push('scripts')
    <script type="text/babel">
        function deleteIndex(id) {
            var index = id.split('delete_button')[1];
            $("#row_add" + index).remove();
        }

        function showtovalue(obj){
            var rowindex = obj.id.substring(9);
            var type = obj.options[obj.selectedIndex].id;
            if (type == 2 || type == 0) {
                $("#tilde" + rowindex).show();
                $("#tovalue" + rowindex).show();
            } else {
                $("#tilde" + rowindex).hide();
                $("#tovalue" + rowindex).hide();
                $("#tokeyword" + rowindex).val('');
            }
        }
        var setT;
        if(appPacAdmin){

            var default_stamp_history_flg = {{ $company_limit->default_stamp_history_flg == 1 ? 1: 0 }};
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($itemsCircular->pluck('id')) !!};
                $scope.inputMaxLength = 46;
                $scope.items = {!! json_encode($itemsCircular) !!};

                $scope.isShowAutomaticUpdate = {!! json_encode($isShowAutomaticUpdate) !!};;
                $scope.isShowLongTermindex = {!! json_encode($isShowLongTermindex) !!};;
                $scope.longTermStorageOptionFlg = {!! json_encode($longTermStorageOptionFlg) !!};;
                $scope.isShowDate = {!! json_encode($isShowDate) !!};;
                $scope.folder = {!! json_encode($folder) !!}
                $scope.company = {!! json_encode($company) !!}

                $scope.automaticOnFlg = "0";
                $scope.search = function () {
                    document.adminForm.submit();
                };

                $scope.rowIndexCnt = {!! json_encode($rowIndexCnt) !!};
                $scope.rowIndex = $scope.rowIndexCnt + 1;
                $scope.longTermIndex = {!! json_encode($longTermIndex) !!};
                $scope.longTermIndexall = {!! json_encode($longTermIndexall) !!};
                $scope.indexes = {!! json_encode($indexes) !!};
                $scope.indexess = [];
                setLongTermIndex();
                $scope.keyword='';
                // PAC_5-2285
                $scope.showDetailId = 0;
                $scope.showLongDetail = {};
                $scope.loginUser = {!! json_encode($loginUser) !!};
                $scope.check_add_stamp_history = false;
                $scope.finishedMonthHidden = '';
                $scope.month = 0;
                $scope.checkFlg=false;
                $scope.checkKeywordsLenFlg = false;
                $scope.checkCircularAttachment = false;
                $scope.folder_id = null;
                $scope.is_sanitizing = {{ $company->sanitizing_flg }};// PAC_5-2853
                $scope.selected_all=false;
                @foreach($itemsCircular as $item)
                        {{'$scope.selected_id'.$item->id.'=false;'}}
                        @endforeach
                $scope.selectRow = function(id){
                    if($scope.selectedID == id) $scope.selectedID = null;
                    else $scope.selectedID = id;
                };

                //親ファイルの取得
                $scope.openFolderNode = function (folder_id){
                    let className = $('.' + folder_id)[0].className;
                    $('.' + folder_id)[0].className = className + ' open';
                }
                //選択したフォルダを選択
                $scope.showSelectedFloder = function () {
                    if ($scope.folder['folder_id']) {
                        if ($scope.folder['parent_folder_id']) {
                            let className = $('.parent')[0].className;
                            $('.parent')[0].className = className + ' open';
                            for (let i = 0; i < $scope.folder['parent_folder_id'].length; i++) {
                                $scope.openFolderNode($scope.folder['parent_folder_id'][i]);
                            }
                        }
                        $scope.selectRow($scope.folder['folder_id']);
                    } else {
                        $scope.selectRow(0);
                    }
                }

                $scope.showSelectedFloder();

                $scope.showDetail = function(id){
                    $rootScope.$emit("showLoading");
                    $scope.showDetailId = id;
                    $scope.checkCircularAttachment = false;
                    $scope.check_add_stamp_history = false;
                    var item_data = $scope.items['data'].filter(function (item, index) {
                        if (item.id == id) return true;
                    });
                    let file_size = Math.round(item_data[0].file_size*100/(1024*1024))/100;
                    $scope.showLongDetail = item_data[0];
                    $scope.showLongDetail.file_size_mb = file_size;
                    $scope.showLongDetail.sender_emails =$scope.showLongDetail.upload_status?'-': $scope.showLongDetail.sender_name + ' <' + $scope.showLongDetail.sender_email + '>';
                    $scope.showLongDetail.destination_emails = '';
                    let destination_email = $scope.showLongDetail.destination_email.split(',');
                    let destination_name = $scope.showLongDetail.destination_name.split(',');
                    for (var i = 0; i < destination_email.length; i++) {
                        if (i < destination_name.length) {
                            $scope.showLongDetail.destination_emails += destination_name[i] + ' &lt;' + destination_email[i] + '&gt;<br/>';
                        } else {
                            $scope.showLongDetail.destination_emails += '&lt;' + destination_email[i] + '&gt;<br/>';
                        }

                    }
                    if($scope.showLongDetail.upload_status){
                        $scope.showLongDetail.destination_emails='-'
                    }
                    $scope.showLongDetail.circular_index.forEach(function (item) {
                        switch (item.data_type) {
                            case 0:
                                item.value = filterNum(item.num_value);
                                break;
                            case 1:
                                item.value = item.string_value;
                                break;
                            case 2:
                                item.value = filterFormatDate(item.date_value);
                                break;
                        }
                    });
                    if($scope.showLongDetail.circular_attachment_name_string != ''){
                        $scope.checkCircularAttachment = true;
                    }
                    let complatedDate = $scope.showLongDetail.completed_at.split('-');
                    let nowDate = formatDate(new Date()).split('-');
                    $scope.month = parseInt(nowDate[0]) * 12 + parseInt(nowDate[1]) - (parseInt(complatedDate[0]) * 12 + parseInt(complatedDate[1]));
                    $scope.finishedMonthHidden = complatedDate[0]+complatedDate[1];
                    $http.post(link_ajax + "/getPreview", {id: $scope.showLongDetail.id, finishedDate: $scope.month})
                        .then(function (event) {
                            $("#destination_emails").html($scope.showLongDetail.destination_emails);
                            $("#circular_attachment_name_string").html($scope.showLongDetail.circular_attachment_name_string);
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $scope.showLongDetail.first_page_data = event.data.first_page_data;
                            }
                        });
                };
                function getUploadStatus() {
                    let s=[];
                    $('.cid').each(function (){
                        if($(this).prop("checked")){
                            s.push($(this).data('status'))
                        }
                    })
                  $scope.checkFlg=s.some(function (item){
                      return item
                  })
                }
                $scope.changeHistory = function() {
                    if($scope.check_add_stamp_history){
                        $scope.check_add_stamp_history = false;
                    }else{
                        $scope.check_add_stamp_history = true;
                    }
                }

                if(default_stamp_history_flg == 1){$scope.changeHistory();}
                function formatDate(dt) {
                    var y = dt.getFullYear();
                    var m = ('00' + (dt.getMonth()+1)).slice(-2);
                    var d = ('00' + dt.getDate()).slice(-2);
                    return (y + '-' + m + '-' + d + ' '+ dt.getHours() +":"+ dt.getMinutes());
                }

                $scope.onUpdateDocument = function() {
                    $rootScope.$emit("showLoading");
                    if($scope.showLongDetail) {
                        if($scope.showLongDetail.keyword.length > 200){
                            $scope.checkKeywordsLenFlg = true;
                        }else{
                            $scope.checkKeywordsLenFlg = false;
                            $http.post(link_ajax + "/updateDocument", {detail: $scope.showLongDetail})
                                .then(function (event) {
                                    $rootScope.$emit("hideLoading");
                                    if (event.data.status == false) {
                                        $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                    } else {
                                        location.reload();
                                        $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                });
                        }
                    }
                }
                function setLongTermIndex(){
                    const longtermIndex = {!! json_encode($longTermIndexall) !!};
                    const fields = Object.keys(longtermIndex).map(function(e) {
                        return longtermIndex[e]
                    }); // workaround: array の場合とそうでない場合の両方に対応するため
                    for (let i=0;i<fields.length;i++) {
                        fields[i].data_type = ["number", "text", "date"][fields[i].type];
                    }
                    $scope.longTermdatas = longtermIndex;
                    const newArr=JSON.parse(JSON.stringify(longtermIndex));
                    const indexTmp=["取引年月日","金額","取引先"]
                    const index1=[]
                    newArr.forEach(function (item){
                        if(indexTmp.indexOf(item.value)!== -1){
                            index1.push({longterm_index_id: item.id, value: "", type:item.type,index_name:item.value,data_type:["number", "text", "date"][item.type]})
                        }
                    })
                    $scope.indexess=index1;
                }
                function filterFormatDate(str) {
                    let date = new Date(str)
                    let y = date.getFullYear();
                    let m = (date.getMonth()+1 + '').padStart(2,'0');
                    let d = (date.getDate() + '').padStart(2,'0');
                    return y + '-' + m + '-' + d;
                }
                function filterNum(num){
                    num= (num+'').replace(/,/g,"");
                    if(num)num = num.split(".");
                    var arr = num[0].split("").reverse();
                    var res = [];
                    for (var i = 0, len = arr.length; i < len; i++) {
                        if (i % 3 === 0 && i !== 0) {
                            res.push(",");
                        }
                        res.push(arr[i]);
                    }
                    res.reverse();
                    if (num[1]) {
                        res = res.join("").concat("." + num[1]);
                    } else {
                        res = res.join("");
                    }
                    return res;
                }
                $scope.onSingleDeleteDocumentClick = function () {
                    $rootScope.$emit("showMocalConfirm", {
                        title:'選択された回覧を削除します。よろしいですか？',
                        btnDanger:'削除',
                        callDanger: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_ajax + "/deleteDetail", {detail: $scope.showLongDetail})
                                .then(function (event) {
                                    $rootScope.$emit("hideLoading");
                                    if (event.data.status == false) {
                                        $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                    } else {
                                        location.reload();
                                        $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    }
                                });
                        }
                    });
                }
                $scope.downloadAttachment = function () {
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax + "/downloadAttachment", {detail: $scope.showLongDetail})
                        .then(function (event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                const data = event.data;
                                // ファイル名とファイルデータから、ダウンロードファイルを作成
                                if(data.fileName && data.file_data){
                                    let bytes = window.atob(data.file_data);
                                    let ab = new ArrayBuffer(bytes.length);
                                    let ia = new Uint8Array(ab);
                                    for (let i = 0; i < bytes.length; i++) {
                                        ia[i] = bytes.charCodeAt(i);
                                    }
                                    let blob = new Blob([ab]);
                                    let downloadElement = document.createElement("a");
                                    let href = window.URL.createObjectURL(blob);
                                    downloadElement.href = href;
                                    downloadElement.download = data.fileName;
                                    document.body.appendChild(downloadElement);
                                    downloadElement.click();
                                    document.body.removeChild(downloadElement);
                                    window.URL.revokeObjectURL(href);
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 30);
                                }
                            }
                        });
                }
                // PAC_5-2285

                $scope.addIndex = function () {
                    var str = '<div class="row" id = "row_add'+ $scope.rowIndex +'">';
                    str += '<div class="col-lg-2"></div>';
                    str += '<div class="col-lg-10">';
                    str += '<div class="row form-group">';
                    str += '<div class="col-lg-3"><select name="longIndex'+ $scope.rowIndex +'" class="form-control" id="longIndex'+ $scope.rowIndex +'" onchange="showtovalue(this)">';
                    str += '<option value=""></option>';
                    for(let index in $scope.longTermIndexall) {
                        str += '<option value="' + $scope.longTermIndexall[index].id + '" id="' + $scope.longTermIndexall[index].type + '" >'+ $scope.longTermIndexall[index].value +'</option>';
                    };

                    str += '</select></div>';
                    str += '<div class="col-md-3"><input name="fromkeyword'+ $scope.rowIndex +'" value="" class="form-control" id="fromkeyword'+ $scope.rowIndex +'"></div>';
                    str += '<label for="name" class="col-md-1 control-label" id="tilde'+ $scope.rowIndex +'">~</label>';
                    str += '<div class="col-md-3" id="tovalue'+ $scope.rowIndex +'"><input name="tokeyword'+ $scope.rowIndex +'" value="" class="form-control" id="tokeyword'+ $scope.rowIndex +'"></div>';
                    str += '<div class="col-md-2">';
                    str += '<div class="btn btn-danger " onclick="deleteIndex(this.id)" id="delete_button'+ $scope.rowIndex +'"> x</div>';
                    str += '</div></div></div></div>';
                    $('#row_add').before(str);
                    $scope.rowIndex ++;
                };

                $scope.download = function (detail) {
                    if($scope.is_sanitizing){
                        $rootScope.$emit("showLoading");
                        $http.post(link_ajax + "/download", {
                            ids: detail ? [$scope.showLongDetail.id] : $scope.selected,
                            checkHistory: $scope.check_add_stamp_history
                        })
                            .then(function (event) {
                                $rootScope.$emit("hideLoading");
                                if (event.data.status == false) {
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                } else {
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                }
                            });
                    }else {
                        $rootScope.$emit("showMocalConfirm",
                            {
                                title: '選択文書ダウンロード',
                                btnSuccess: 'はい',
                                size: 'lg',
                                inputEnable: 'true',
                                inputMaxLength: $scope.inputMaxLength,
                                callSuccess: function (inputData) {
                                    $rootScope.$emit("showLoading");
                                    $http.post(link_ajax + "/download", {
                                        ids: detail ? [$scope.showLongDetail.id] : $scope.selected,
                                        fileName: inputData.val(),
                                        checkHistory: $scope.check_add_stamp_history
                                    })
                                        .then(function (event) {
                                            $rootScope.$emit("hideLoading");
                                            if (event.data.status == false) {
                                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                            } else {
                                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                            }
                                        });
                                }
                            });
                    }
                };
                $scope.toogleCheckAll = function () {
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if ($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                    $scope.inputMaxLength = 46;
                    getUploadStatus()
                    $scope.mapArrayCheck($scope.selected);
                };
                var clickCheck = false;
                $scope.toogleCheck = function (id) {
                    if(clickCheck == false){
                        clickCheck = true;
                    }else{
                        return ;
                    }
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                        $('#selected_id'+id).prop("checked",false); 
                    } else {
                        $scope.selected.push(id);
                        $('#selected_id'+id).prop("checked",true);
                    }
                    getUploadStatus()
                    var item_data = $scope.items['data'].filter(function (item, index) {
                        if (item.id == $scope.selected[0]) return true;
                    });
                    if ($scope.selected.length == 1 && item_data[0].file_names &&item_data[0].file_names.indexOf(',')!=-1&& item_data[0].file_names.split(',').length == 1) {
                        // // .pdf, .docx, .xlsx
                        var pos = item_data[0].file_names.lastIndexOf('.');
                        $scope.inputMaxLength = 50 - item_data[0].file_names.substr(pos).length;
                    } else {
                        // .zip
                        $scope.inputMaxLength = 46;
                    }
                    $scope.mapArrayCheck([id]);
                    clickCheck = false;
                };
                $scope.mapArrayCheck = function (ids) {
                    for(let i = 0; i < ids.length; i++){
                        for (let j = 0; j < $scope.items.data.length; j ++){
                            if(ids[i] == $scope.items.data[j].id && $scope.items.data[j].upload_status){
                                $scope.check_add_stamp_history = false;
                                return ;
                            }
                        }
                    }  
                };
                $scope.delete = function () {
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title: '選択された回覧を削除します。よろしいですか？',
                            btnDanger: '削除',
                            callDanger: function () {
                                document.adminForm.action.value = 'delete';
                                document.adminForm.submit();
                            }
                        });

                };

                $scope.remove = function () {
                    $rootScope.$emit("showRemoveFolderTreeConfirm",
                        {
                            btnSuccess: 'はい',
                            size: 'lg',
                            company_name: $scope.company.company_name,
                            folder_id: $scope.folder_id,
                            callSuccess: function (inputData) {
                                $http.post(link_ajax + "/removeFolder", {ids: $scope.selected,folder_id: inputData.folder_id})
                                    .then(function (event) {
                                        $rootScope.$emit("hideLoading");
                                        if (event.data.status == false) {
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        } else {
                                            location.reload();
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    })
                            }
                        });
                }

                $scope.automaticUpdateClick = function (onFlg) {
                    document.adminForm.automaticOnFlg.value = onFlg;
                    document.adminForm.action.value = 'automaticUpdateClick';
                    document.adminForm.submit();

                };

                if ($scope.company.long_term_folder_flg){
                    displayWindowSize();
                }

                $(window).resize(function (){
                    if ($scope.company.long_term_folder_flg){
                        displayWindowSize();
                    }
                });

                $scope.saveLongTerm =function () {
                    $rootScope.$emit("showLongTermSaveMocalConfirm",
                        {
                            title: '確認',
                            btnSuccess: '登録',
                            size: 'lg',
                            inputEnable: 'true',
                            keyword: $scope.keyword,
                            inputMaxLength: $scope.inputMaxLength,
                            longTermdatas: $scope.longTermdatas,
                            indexess: $scope.indexess,
                            long_term_storage_option_flg: $scope.longTermStorageOptionFlg,
                            long_term_folder_flg: $scope.company['long_term_folder_flg'],
                            company_name: $scope.company['company_name'],
                            folder_id: $scope.folder_id,
                            callSuccess: function (inputData) {
                                if(inputData.file==undefined){
                                    $(".message-list").append(showMessages(['登録するファイルがアップロードされていません'], 'danger', 10000));

                                }else {
                                    let data = {
                                        file_name:inputData.file.file_name,
                                        upload_id:inputData.file.upload_id,
                                        keyword: inputData.keyword,
                                        indexes: $scope.indexess,
                                        finishedMonthHidden: $scope.finishedMonthHidden ? $scope.finishedMonthHidden : '',
                                        folder_id: inputData.folderId,
                                    }
                                    let flg = true;
                                    for (let v in $scope.indexess) {

                                        if($scope.indexess[v].type === 2 && $scope.indexess[v].value){
                                            $scope.indexess[v].value=new Date($scope.indexess[v].value).toLocaleDateString();
                                        }
                                        if ($scope.indexess[v].type === 1 && $scope.indexess[v].value) {
                                            if ($scope.indexess[v].value.length > 128) {
                                                flg = false
                                            }
                                        }
                                        $scope.indexess[v][$scope.indexess[v].index_name] = $scope.indexess[v].value
                                    }
                                    if (flg) {
                                        $http.post(link_ajax + "/saveLongTermDocument", data).then(function (event) {
                                            $rootScope.$emit("hideLoading");
                                            if (event.data.status == false) {
                                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                            } else {

                                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                                setT=   setTimeout(function (){
                                                    $scope.search()
                                                },1000)
                                            }
                                        });
                                    }else {
                                        $(".message-list").append(showMessages(['長期保管インデックス文字列の長さは128ビット以上に設定できません。'], 'danger', 10000));
                                    }
                                }

                            }

                        });
                }
                $rootScope.$on("addLongTermIndex",function (event,data){
                    $scope.indexess.push(data)
                });
                $rootScope.$on("removeLongTermIndex",function (event,data){
                    $scope.indexess.splice(data,1)
                });
                $rootScope.$on("intLongTermIndex",function (event,data){
                    setLongTermIndex()
                });
                $rootScope.$on("intKeyword",function (event,data){
                    $scope.keyword=''
                });
                $rootScope.$on("intFolderId",function (event,data){
                    $scope.folder_id = null
                });
            });
        }

        $(document).ready(function() {
            $('select[name="limit"]').change(function () {
                document.adminForm.submit();
            });
        });

        $(document).ready(function() {
            clearTimeout(setT)
            $('.select2').select2({
                placeholder: '',
                allowClear: true,
                "language": {
                    "noResults": function(){
                        return "データがありません";
                    }
                }
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
        function displayWindowSize(){
            if (document.body.clientWidth < 975) {
                $('.folder_card').removeClass('folder-height-big').addClass('folder-height-sm');
                $('.main_card').removeClass('card-big').addClass('card-sm');
            }else {
                $('.folder_card').removeClass('folder-height-sm').addClass('folder-height-big');
                $('.main_card').removeClass('card-sm').addClass('card-big');
            }
        }
    </script>
     <script>
    document.oncontextmenu = function () {return false;}
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
        .dropdown:hover>.dropdown-menu {
            display: block;
        }
        .dropdown>.dropdown-toggle:active {
            pointer-events: none;
        }
        .info{  padding: 3px 3px 3px 5px; }
        .folder-height-big{
            position: fixed;
            height: 80%;
        }
        .folder-height-sm{
            height: 10%;
            padding-bottom: 20px;
        }
        .active-folder{
            width: 22%;
        }
        .card-big{
            padding-left: 27%;
            padding-right: 20px;
        }
        .card-sm{
            padding-left: 20px;
            padding-right: 20px;
        }
        .name:hover{
            background-color: #e7f4f9;
        }
        .tooltip-inner{
            max-width: 100%;
        }
    </style>
@endpush
