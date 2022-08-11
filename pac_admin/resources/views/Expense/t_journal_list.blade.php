<div ng-controller="ListController" class="list-view">
	<form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
	@csrf
	    <div class="form-search form-vertical">
	        <div class="row">
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">計上日From</label>
	                    <input type="date" name="rec_from" value="{{ Request::get('rec_from', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="rec_from">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">計上日To</label>
	                    <input type="date" name="rec_to" value="{{ Request::get('rec_to', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="rec_to">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">支払日From</label>
	                    <input type="date" name="expected_pay_from" value="{{ Request::get('expected_pay_from', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="expected_pay_from">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-8 control-label">支払日To</label>
	                    <input type="date" name="expected_pay_to" value="{{ Request::get('expected_pay_to', '') }}" class="form-control" placeholder="yyyy/mm/dd" id="expected_pay_to">
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="form-group">
	                    <label for="name" class="col-md-16 control-label">勘定科目・補助科目　空検索</label>
						<div class="col-lg-3">
							<label for="accountspace" class="control-label"><input type="checkbox" value = "1" name="accountspace" {{ Request::get('accountspace') ? 'checked' : '' }} id="accountspace" /></label>
						</div>
	                </div>
	            </div>
	            <div class="col-lg-2">
	                <div class="text-right mt-4">
	                    <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
	                </div>
	            </div>
	        </div>

	    </div>

	    <div class="message message-list mt-3"></div>
	    <div class="card mt-3">
	        <div class="card-header">経費仕訳一覧</div>
	        <div class="card-body ">
	            <span class="clear"></span>

				<button type="button" ng-click="downloadCsv()" class="btn btn-warning mb-1" ng-disabled="selected.length==0"><i class="fas fa-download"></i> ダウンロード予約</button>
	            <table class="tablesaw-list tablesaw adminlist mt-1" data-tablesaw-mode="swipe">
	                <thead class="th_color">
	                    <tr>
	                        <th rowspan="3" class="sort table-bordered" scope="col" data-tablesaw-priority="persist">
	                            <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>
	                        </th>
	                        <th rowspan="3" scope="col" class="sort td_right_w" style="width: 180px">計上日
	                        </th>
	                        <th rowspan="3" scope="col" class="sort td_right_w" style="width: 180px">支払日
	                        </th>
							<th colspan="5" scope="col" class="title td_right_w">借方</th>
    						<th colspan="5" scope="col" class="title table-bordered">貸方</th>
    						<th rowspan="3" scope="col" class="title table-bordered" style="width: 60px"></th>
							<tr>
								<th scope="col" class="sort table-bordered" style="width: 180px">勘定科目
								</th>
								<th scope="col" class="sort table-bordered" style="width: 180px">補助科目
								</th>
								<th scope="col" class="sort table-bordered text-right" style="width: 180px">金額
								</th>
								<th scope="col" class="sort table-bordered" style="width: 120px">税区分
								</th>
								<th scope="col" class="sort td_right_w text-right" style="width: 120px">税額　
								</th>
								<th scope="col" class="sort table-bordered" style="width: 180px">勘定科目
								</th>
								<th scope="col" class="sort table-bordered" style="width: 180px">補助科目
								</th>
								<th scope="col" class="sort table-bordered text-right" style="width: 180px">金額　
								</th>
								<th scope="col" class="sort table-bordered" style="width: 120px">税区分
								</th>
								<th scope="col" class="sort table-bordered text-right" style="width: 120px">税額　
								</th>
  						    </tr>
 						    <tr>
								<th  colspan="10" scope="col" class="sort table-bordered">摘要
								</th>
							</tr>
	                    </tr>
	                </thead>
	                <tbody>
	                    @if ($arrApp)
	                    @foreach ($arrApp as $i => $item)
	                        <tr class="row-{{ $item->id }} {{$item->rownum_flg}} row-edit" ng-click="detailsRecord({{ $item->eps_t_app_id}},{{$item->eps_t_app_item_id}},{{$item->eps_app_item_bno}},{{$item->id }})">
	                            <!-- 明細行 -->
	                            <td  rowspan="2" class="table-bordered">
	                                <input type="checkbox" value="{{ $item->id }}" ng-click="toogleCheck({{ $item->id }})"
	                                    name="cids[]" class="cid" onClick="isChecked(this.checked)" />
	                            </td>
								<td  rowspan="2" class="td_right_w" ng-class="{ edit: id == {{ $item->id }} }" >{{ date("Y/m/d", strtotime($item->rec_date)) }}</td>
								<td  rowspan="2" class="td_right_w" ng-class="{ edit: id == {{ $item->id }} }" >{{ date("Y/m/d", strtotime($item->expected_pay_date)) }}</td>
   	 							    <td class="table-bordered " ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->debit_account }}</td>
   	 							    <td class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->debit_subaccount }}</td>
   	 							    <td class="table-bordered {{$item->debit_amount_style}} text-right" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->debit_amount }}</td>
   	 							    <td class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >{{ \App\Http\Utils\AppUtils::TAX_DIV_LIST[$item->debit_tax_div]}}</td>
   	 							    <td class="td_right_w text-right" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->debit_tax }}</td>
   	 							    <td class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->credit_account }}</td>
   	 							    <td class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->credit_subaccount }}</td>
   	 							    <td class="table-bordered {{$item->credit_amount_style}} text-right" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->credit_amount }}</td>
   	 							    <td class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >{{ \App\Http\Utils\AppUtils::TAX_DIV_LIST[$item->credit_tax_div]}}</td>
   	 							    <td class="table-bordered text-right" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->credit_tax }}</td>
								<td  rowspan="2" class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >
									@canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_UPDATE])
									<div>
								      <a ng-click="detailsRecord({{ $item->eps_t_app_id}},{{$item->eps_t_app_item_id}},{{$item->eps_app_item_bno}},{{$item->id }})" href="">修正</a>
									</div>
									@endcanany
									@canany([PermissionUtils::PERMISSION_EXPENSE_JOURNAL_LIST_CREATE])
									<div>
	    							  <a ng-click="detailsRecord({{ $item->eps_t_app_id}},{{$item->eps_t_app_item_id}},{{$item->eps_app_item_bno}},{{$item->id }} )" href="">追加</a>
									</div>
									@endcanany
								</td>
								<tr class="row-{{ $item->id }} {{$item->rownum_flg}} row-edit" ng-click="detailsRecord({{ $item->eps_t_app_id}},{{$item->eps_t_app_item_id}},{{$item->eps_app_item_bno}},{{$item->id }})">
									<td colspan="10" class="table-bordered" ng-class="{ edit: id == {{ $item->id }} }" >{{ $item->remarks }}</td>
    							</tr>
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
	    <input type="hidden" name="page" value="1">
	    <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
	</form>
</div>

@push('styles_after')
    <style>
		.td_right_w{
            border:       solid  1px #dcdcdc;
            border-right: double 5px #dcdcdc;
        }
		.th_color { background-color: #eee;}
		.even { background-color: #f6f6f6;}
		.style_red { background-color: #f00;}
    </style>
@endpush


@push('scripts')
    <script>
		$(function()
		{
		})
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($arrApp->pluck('id')) !!};
                $scope.detaildata = [];

				//明細行色のストライプ対応 1行おき
				// $("table").each(function()
				// {
				// 	var isLine = true;
				// 	// var numTh = $(this).find("th").length;
				// 	var numTh = $(this).find("th").length -3;

				// 	$(this).find("tr").each(function()
				// 	{
				// 		if (numTh == $(this).find("td").length)
				// 		{
				// 			isLine = !isLine;
				// 		}
				// 		if (isLine == true)
				// 		{
				// 			$(this).find("td").addClass("even");
				// 		}
				// 	});
				// });

                $scope.detailsRecord = function(eps_t_app_id,eps_t_app_item_id,eps_app_item_bno,id){
                    $rootScope.$emit("openDetails",{eps_t_app_id:eps_t_app_id, eps_t_app_item_id:eps_t_app_item_id, eps_app_item_bno:eps_app_item_bno, id:id});
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
                            title:'仕訳データを出力します。<br/>実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                var data = {
                                    cids: $scope.selected,
                                    rec_from: "{!! Request::get('rec_from', '') !!}",
                                    rec_to: "{!! Request::get('rec_to', '') !!}",
                                    expected_pay_from: "{!! Request::get('expected_pay_from', '') !!}",
                                    expected_pay_to: "{!! Request::get('expected_pay_to', '') !!}",
                                    accountspace: "{!! Request::get('accountspace', '') !!}",
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
