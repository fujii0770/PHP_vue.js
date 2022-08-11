<div ng-controller="ListController" class="list-view">
	<form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
	@csrf
	    <div class="form-search form-vertical">
	        <div class="row">
	            <div class="col-lg-2"></div>
	            <div class="col-lg-2">
	                {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('id','　申請ID',Request::get('id', ''),'text', false,
	                [ 'placeholder' =>'申請ID' ]) !!}
	            </div>
                <div class="col-lg-8  form-vertical">
					<div class="col-lg-5">
						<label for="beforeapp" class="control-label"><input type="checkbox" value = "1" name="beforeapp" {{ Request::get('beforeapp') ? 'checked' : '' }} id="beforeapp" /> 事前申請　</label>
					</div>
  					<div class="col-lg-3">
					    <label for="eps" class="control-label"><input type="checkbox" value = "2" name="eps" {{ Request::get('eps') ? 'checked' : '' }} id="eps" /> 精算</label>
					</div>
                </div>
	        </div>

			<div class="row">
	            <div class="col-lg-2"></div>
	            <div class="col-lg-2">
	                {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('form_code','　様式コード',Request::get('form_code', ''),'text', false,
	                [ 'placeholder' =>'様式コード' ]) !!}
	            </div>
	            <div class="col-lg-2">
	                {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('form_name','　様式名',Request::get('form_name', ''),'text', false,
	                [ 'placeholder' =>'様式名' ]) !!}
	            </div>
	            <div class="col-lg-2">
	                {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('username','　申請者',Request::get('username', ''),'text', false,
	                [ 'placeholder' =>'申請者' ]) !!}
	            </div>
				<div class="col-lg-2">
	                <div class="text-right mt-4">
	                    <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
	                </div>
	            </div>
	        </div>
	        <div class="row">
				<div class="col-lg-2"></div>
				<div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">提出日From</label>
	                    <input type="date" name="submission_from" value="{{ Request::get('filing_date_from', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="submission_from">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">提出日To</label>
	                    <input type="date" name="submission_to" value="{{ Request::get('filing_date_to', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="submission_to">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">承認日From</label>
	                    <input type="date" name="diff_from" value="{{ Request::get('completed_date_from', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="diff_from">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">承認日To</label>
	                    <input type="date" name="diff_to" value="{{ Request::get('completed_date_to', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="diff_to">
	                </div>
	            </div>
	        </div>

	    </div>

	    <div class="message message-list mt-3"></div>
	    <div class="card mt-3">
	        <div class="card-header">経費申請一覧</div>
	        <div class="card-body">
	            <span class="clear"></span>

				<button type="button" ng-click="downloadCsv()" class="btn btn-warning mb-1" ng-disabled="selected.length==0"><i class="fas fa-download"></i> ダウンロード予約</button>
	            <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
	                <thead>
	                    <tr>
	                        <th class="title sort" scope="col" data-tablesaw-priority="persist">
	                            <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>
	                        </th>
	                        <th scope="col" class="sort" style="width: 180px">
	                            {!! \App\Http\Utils\CommonUtils::showSortColumn('様式名', 'form_code', $orderBy, $orderDir) !!}
	                        </th>
							<th scope="col" class="sort" style="width: 400px">
	                            {!! \App\Http\Utils\CommonUtils::showSortColumn('目的名', 'purpose_name', $orderBy, $orderDir) !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 400px">
	                            {!! \App\Http\Utils\CommonUtils::showSortColumn('申請者(メールアドレス)', 'user_name', $orderBy, $orderDir) !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 180px ">
	                            {!! \App\Http\Utils\CommonUtils::showSortColumn('申請金額', 'eps_amt', $orderBy, $orderDir) !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 180px">
	                            {!! \App\Http\Utils\CommonUtils::showSortColumn('提出日', 'filing_date', $orderBy, $orderDir) !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 180px">
	                            {!! \App\Http\Utils\CommonUtils::showSortColumn('承認日', 'completed_date', $orderBy, $orderDir) !!}
	                        </th>
	                    </tr>
	                </thead>
	                <tbody>
	                    @if ($arrApp)
	                    @foreach ($arrApp as $i => $item)
	                        <tr class="row-{{ $item->id }} row-edit">
	                            <!-- 明細行 -->
	                            <td class="title">
	                                <input type="checkbox" value="{{ $item->id }}" ng-click="toogleCheck({{ $item->id }})"
	                                    name="cids[]" class="cid" onClick="isChecked(this.checked)" />
	                            </td>
								<td class="title" ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ $item->form_name }}</td>
								<td ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ $item->purpose_name }}</td>
								<td class="title" ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ $item->user_name."(".$item->email.")" }}</td>
								<td class="title" ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ $item->eps_amt }}</td>
								@if($item->filing_date==null)
								<td ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{$item->filing_date}}</td>
								@else
								<td ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ date("Y/m/d", strtotime($item->filing_date)) }}</td>
								@endif
								@if($item->completed_date==null)
								<td ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ $item->completed_date}}</td>
								@else
								<td ng-class="{ edit: id == {{ $item->id }} }" ng-click="detailsRecord({{ $item->id }})">{{ date("Y/m/d",strtotime($item->completed_date))}}</td>						
								@endif
	                        </tr>
	                    @endforeach
	                    @endif
	                </tbody>
	            </table>
	            @if ($arrApp)
	            @include('layouts.table_footer',['data' => $arrApp])
	            @endif
	        </div>
	    </div>
	    <% boxchecked %>
	    <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
	    <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
	    <input type="hidden" name="page" value="1">
	    <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
	</form>
</div>


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($arrApp->pluck('detail_id')) !!};

                $scope.approval = function(event){
                    event.preventDefault();

                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'選択された勤務を承認します。',
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_update_select, { chks:$scope.selected }).then(function(event) {
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

                $scope.detailsRecord = function(id){
                    //  idがマイナスなら詳細表示ボタンから
                    if( id < 0) {
                        id = $scope.selected;
                    }
                    $rootScope.$emit("openDetailsAttendance",id);
                };

                $scope.toogleCheckAll = function(){
                    $scope.isCheckAll = !$scope.isCheckAll;
                    if($scope.isCheckAll)
                        $scope.selected = $scope.cids;
                    else $scope.selected = [];
                };

                $scope.toogleCheck = function(id){
                    var idx = $scope.selected.indexOf(id);
                    if (idx > -1) {
                        $scope.selected.splice(idx, 1);
                    }else{
                        $scope.selected.push(id);
                    }
                };

				$scope.downloadCsv = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'申請データを出力します。<br/>実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                var data = {
                                    cids: $scope.selected,
									beforeapp: "{!! Request::get('beforeapp', '') !!}",
									eps: "{!! Request::get('eps', '') !!}",
                                    id: "{!! Request::get('id', '') !!}",
                                    form_code: "{!! Request::get('form_code', '') !!}",
                                    form_name: "{!! Request::get('form_name', '') !!}",
                                    submission_from: "{!! Request::get('submission_from', '') !!}",
                                    submission_to: "{!! Request::get('submission_to', '') !!}",
                                    username: "{!! Request::get('username', '') !!}",
                                    suspay_from: "{!! Request::get('suspay_from', '') !!}",
                                    suspay_to: "{!! Request::get('suspay_to', '') !!}",
                                    diff_from: "{!! Request::get('diff_from', '') !!}",
                                    diff_to: "{!! Request::get('diff_to', '') !!}",
                                };
                                $http.post(link_ajax_csv, data)
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
            });
        }

        $("#modalDetailItem").on('hide.bs.modal', function () {
            $(".adminlist tr.edit").removeClass('edit');
            if(hasChange){
                location.reload();
            }
        });
    </script>
@endpush
