<div ng-controller="ListController" class="list-view">
	<form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
	@csrf
	    <div class="form-search form-vertical">
	    </div>

		<input type="hidden" name="mst_user_id" value="{{ Request::get('mst_user_id', $mst_user_id) }}" class="form-control" placeholder="yyyy/mm" id="mst_user_id">
		<input type="hidden" name="work_month" value="{{ Request::get('work_month', $work_month) }}" class="form-control" placeholder="yyyy/mm" id="work_month">

		<div class="form-group">
			<div class="row">
				<div class="col-lg-12 text-right">
					<button type="button" class="btn btn-success mb-1" ng-click="download()">CSV出力</button>
					@canany([PermissionUtils::PERMISSION_SCHEDULE_LIST_SETTING_UPDATE])
					<button type="button" class="btn btn-success mb-1" ng-click="approval($event, true)">一括承認</button>
					<button type="button" class="btn btn-success mb-1" ng-click="approval($event, false)">差戻し</button>
					@endcanany
				</div>
			</div>
		</div>

		<div class="message message-list mt-3"></div>
	    <div class="card mt-3">
	        <div class="card-header">
				<div class="row align-items-center m-0">
				    {!! $username !!}　　{!! substr($work_month,0,4) !!}年{!! substr($work_month,4,2) !!}月
					<div class="col-lg-10 text-right">
						<button class="btn btn-link p-2" ng-click="addMonthSearch(-1); $event.preventDefault();">
							<div class="hl__yearBtn hl__yearBtn--prev"></div>
						</button>
						
						<button class="btn btn-link p-2" ng-click="addMonthSearch(1); $event.preventDefault();">
							<div class="hl__yearBtn hl__yearBtn--next"></div>
						</button>
					</div>
				</div>
			</div>

	        <div class="card-body">
	            <div class="table-head">
	            </div>

	            <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
	                <thead>
	                    <tr>
	                        <th class="title sort" scope="col"  style="width: 130px" >
	                            {!! '日付' !!}
	                        </th>
	                        <th scope="col" class="sort">
	                            {!! '出勤' !!}
	                        </th>
	                        <th scope="col" class="sort">
	                            {!! '退勤' !!}
	                        </th>
	                        <th scope="col" class="sort">
	                            {!! '休憩時間' !!}
	                        </th>
	                        <th scope="col" class="sort">
	                            {!! '稼働時間' !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 200px">
	                            {!! '休暇等' !!}
	                        </th>
	                        <th scope="col" class="sort">
	                            {!! '承認状態' !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 400px">
	                            {!! '備考' !!}
	                        </th>
	                        <th scope="col" class="sort" style="width: 150px">
	                            {!! '編集' !!}
	                        </th>
	                        <th ng-show="false" class="title sort" scope="col" data-tablesaw-priority="persist">
	                            <input type="checkbox" onClick="checkAll(this.checked)" ng-click="toogleCheckAll()"/>
	                        </th>
	                    </tr>
	                </thead>
	                <tbody>
	                    @if ($arrTimeCard)
	                    @foreach ($arrTimeCard as $i => $item)
		                    @if ($item->detail_id)
	                        <tr class="row-{{ $item->detail_id }} row-edit">
	                            <!-- 明細行 -->
								<td height="50px" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->work_date }}</td>
								<td ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ date("H:i", strtotime($item->work_start_time)) }}</td>
								<td ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->work_end_time }}</td>
								<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->break_time }}</td>
								<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->working_time }}</td>
								@if ($item->vacation_etc)
									<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->vacation_etc}} </td>
								@else
									<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" ></td>
								@endif
								<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ \App\Http\Utils\AppUtils::APPROVAL_STATE[$item->approval_state] }}</td>
								<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->memo }}</td>
								<td class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >
								@canany([PermissionUtils::PERMISSION_SCHEDULE_LIST_SETTING_UPDATE])
									@if ($item->approval_state)
										<button ng-click="detailsRecord({{ $item->detail_id }});$event.preventDefault();" class="btn btn-primary mb-1">編集</button>
									@endif
								@endcanany
								</td>
	                            <td ng-show="false" class="title">
	                                <input type="checkbox"  checked="checked" value="{{ $item->detail_id }}"  name="cids[]" class="cid"/>
	                            </td>
								<td ng-show="false" class="title" ng-class="{ edit: id == {{ $item->detail_id }} }" >{{ $item->detail_id }}</td>
	                        </tr>
							@else
	                        <tr class="row-{{ $item->detail_id }} row-edit">
	                            <!-- 明細行 -->
								<td height="50px">{{ $item->work_date }}</td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
	                        </tr>
							@endif
	                    @endforeach
	                    @endif
	                </tbody>
	            </table>
	        </div>
		</div>
	    <input type="hidden" name="boxchecked" value="0" class="boxchecked" ng-model="boxchecked" />
	</form>
	@include('partial.form_export_timecard_csv')
</div>

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){
                $scope.selected = [];
                $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($arrTimCard2->pluck('detail_id')) !!};
				$scope.mst_user_id = {{$mst_user_id}};
				$scope.work_month  = {{$work_month}};
				$scope.selected = $scope.cids; //全選択された状態にする
				$scope.outputList = {
					work_date: 1,
					work_start_time: 1,
					work_end_time: 1,
					break_time: 1,
					working_time: 1,
					overtime: 1,
					late_flg: 1,
					earlyleave_flg: 1,
					paid_vacation_flg: 1,
					sp_vacation_flg: 1,
					day_off_flg: 1,
					memo: 1,
					admin_memo: 1
				};
                $scope.approval = function(event, isApproval){
                    event.preventDefault();
                    let confirmTitle = '選択された勤務を承認します。';
                    if (!isApproval){
                    	confirmTitle = '選択された勤務を差戻します';
					}
                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:confirmTitle,
                        btnSuccess:'はい',
                        callSuccess: function(){
                            $rootScope.$emit("showLoading");
                            $http.post(link_update_select, { chks:$scope.selected,mst_user_id:$scope.mst_user_id ,work_month:$scope.work_month, isApproval: isApproval}).then(function(event) {
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

                //日付ボタン押下時
                $scope.addMonthSearch = function(add_value){
					var wokmonth = document.getElementById("work_month").value
 					var dt = new Date(wokmonth.substring(0,4), wokmonth.substring(4) -1 , 1);
					dt.setMonth(dt.getMonth() + add_value);
					//dt.addMonth();
                    document.getElementById("work_month").value = dt.getFullYear() + ("00" + (dt.getMonth()+1)).slice(-2); //YYYYMMに変換
                    document.adminForm.submit();
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

                $scope.download = function(){
					$("#modelCSV_OutputList").modal();
                 };
                
				$scope.exportCSV = function () {
					console.log($scope.outputList)
					console.log(['{{$timecard_id}}'])
					$http.post(link_export, {outputList: $scope.outputList, timecard_ids: ['{{$timecard_id}}']})
							.then(function (event) {
								$rootScope.$emit("hideLoading");
								if (event.data.status == false) {
									$("#modelCSV_OutputList .message").append(showMessages(event.data.message, 'danger', 10000));
								} else {
                                    const byteString = Base64.atob(event.data.file_data);
                                    const ab = new ArrayBuffer(byteString.length);
                                    const ia = new Uint8Array(ab);
                                    for (let i = 0; i < byteString.length; i++) {
                                        ia[i] = byteString.charCodeAt(i);
                                    }
                                    const dataBlob = new Blob([ab]);
                                    downloadFile(dataBlob, event.data.fileName);
								}
							});
				}
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
@push('styles_after')
    <style>
        .hl__yearBtn {
            width: 8px;
            height: 8px;
            background-color: gray;
        }

        .hl__yearBtn::after {
            display: block;
            width: 6px;
            height: 6px;
            background-color: #fff;
            transform: translate(-0.5px, -0.5px);
            content: "";
        }

        .hl__yearBtn--prev {
            transform: rotate(135deg);
        }

        .hl__yearBtn--next {
            transform: rotate(-45deg);
        }
    </style>
@endpush
