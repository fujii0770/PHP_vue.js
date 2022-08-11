<div ng-controller="ModalPasswordCodeTableController">

	<div class="modal modal-add-stamp mt-5" id="modalPasswordCodeTable" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-<% size %>" data-backdrop="static" data-keyboard="false">
			<div class="modal-content">            
				<!-- Modal Header -->
				<div class="modal-header">
					<h4 class="modal-title" ng-if="title" ng-bind-html="title"></h4>
				</div>
				<!-- Modal body -->
				<div class="modal-body text-left" ng-if="message" ng-bind-html="message"></div>

				<!-- message -->
				<div class="message message-list m-3"></div>

				<div class="col"> </div>
				<div class="container">
					<div style="align-items: center" class="row justify-content-end">
						<div class="mx-2">
							<button style="font-size:2em; border: none; border-radius:18px;" ng-click="copyToClipboard()"><i class="far fa-clipboard"></i></button>
						</div>
						<div class="mx-2">
							<div class="btn btn-default" ng-if="btnClose" ng-click="close()">
								<i class="fas fa-times-circle"></i> <% btnClose %>
							</div>
						</div>
					</div>
				</div>
				<div class="m-2">
					<div class="overflow-auto" style="max-height:300px;">
						<table class="tablesaw-list tablesaw table-bordered px-2" data-tablesaw-mode="swipe">
							<thead>
								<tr>
									<th>メールアドレス</th>
									<th>氏名</th>
									<th>パスワード設定コード</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="user in rows">
									{{-- メールアドレス --}}
									<td><% user.email %></td>
									{{-- 氏名 --}}
									<td><% user.family_name %> <% user.given_name %></td>
									{{-- パスワード設定コード --}}
									<td><% user.password_code %></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<div class="m-2">
						<button style="font-size:2em; border: none; border-radius:18px;" ng-click="copyToClipboard()"><i class="far fa-clipboard"></i></button>
					</div>
					<div class="btn btn-default" ng-if="btnClose" ng-click="close()">
						<i class="fas fa-times-circle"></i> <% btnClose %>
					</div>
				</div>
			</div>
		</div>
	</div>
  
</div>


@push('scripts')
<script>
	var appPacAdmin = initAngularApp();
	if(appPacAdmin){
		appPacAdmin.controller('ModalPasswordCodeTableController', function($scope, $rootScope){
			$rootScope.$on("showModalPasswordCodeTable", function(event, data){
				data = data || {};
				$scope.title = data.title || "";
				$scope.message = data.message || "";
				$scope.btnClose = data.btnClose || "閉じる";
				$scope.callClose = data.callClose || null;
				$scope.size = data.size || "lg";
				$scope.rows = data.rows || {};
				$scope.headers = data.headers || null;
				
				$("#modalPasswordCodeTable").modal();
			});

			$scope.close = function(){                    
				if($scope.callCancel) $scope.callClose($scope.databack);
				$("#modalPasswordCodeTable").modal("hide");
			}

			// ボタンを押してコードをクリップボードにコピー
			$scope.copyToClipboard = function(event){
				var copyText = "";
				$scope.rows.forEach( function (row) {
					copyText += row.email + ',' + row.family_name + ' ' + row.given_name + ',' + row.password_code + '\r\n';
				});
				var el = document.createElement('textarea');
				el.value = copyText;
				document.body.appendChild(el);
				el.select();
				document.execCommand('copy');
				document.body.removeChild(el);
				$("#modalPasswordCodeTable .message-list").append(showMessages(['クリップボードにコピーしました'], 'success', 1000));
			};
		})
	}else{
		throw new Error("Something error init Angular.");
	}
</script>
@endpush