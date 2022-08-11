<div ng-controller="ListController">
        
    <form action="" name="adminForm" method="GET">
        <div class="form-search form-horizontal">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('mst_company_id','企業ID',Request::get('mst_company_id', ''),'text', false, [ ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('to_email','宛先',Request::get('to_email', ''),'text', false, [ ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">機能</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::getMailDictionary(), 'template', Request::get('template', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="row">
                            <label for="name" class="col-md-4 control-label">送信状態</label>
                            <div class="col-md-8">
                                {!! \App\Http\Utils\CommonUtils::buildSelect(\App\Http\Utils\AppUtils::MAIL_STATE_CODE, 'state', Request::get('state', ''),'',['class'=> 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('create_at_start','送信日時/時刻',Request::get('create_at_start', ''),'datetime-local', false,
                    [ ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('create_at_end','～',Request::get('create_at_end', ''),'datetime-local', false,
                    [ ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('update_at_start','リクエスト日時/時刻',Request::get('update_at_start', ''),'datetime-local', false,
                    [ ]) !!}
                </div>
                <div class="col-lg-4">
                    {!! \App\Http\Utils\CommonUtils::showFormField('update_at_end','～',Request::get('update_at_end', ''),'datetime-local', false,
                    [ ]) !!}
                </div>
                <div class="col-lg-2"></div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary mb-1"><i class="fas fa-search" ></i> 検索</button>
                <input type="hidden" class="action" name="action" value="search" />
            </div>


        </div>

        <div class="message message-list mt-3"></div>
        @if($mailList)
        <div class="card mt-3">
            <div class="card-header">検索結果メール送信一覧</div>
            <div class="card-body">
                <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                    <thead>
                        <tr>
                            <th scope="col">送信日時</th>
                            <th scope="col">企業</th>
                            <th scope="col">企業ID</th>
                            <th scope="col">宛先</th>
                            <th scope="col">機能</th>
                            <th scope="col">発生日時</th>
                            <th scope="col">送信状態</th>
                            <th scope="col">送信回数</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mailList as $i => $mail)
                            <tr name="mail-detail" id="mail-detail-{{ $mail->id }}" ng-click="showDetail({{ $mail->id }})">
                                <td id="test">{{ $mail->update_at }}</td>
                                <td>{{ $mail->company_name?$mail->company_name:'社外' }}</td>
                                <td>{{ $mail->mst_company_id }}</td>
                                <td>{{ $mail->to_email }}</td>
                                <td>{{ \App\Http\Utils\AppUtils::getMailDictionaryByCode($mail->template) }}</td>
                                <td>{{ $mail->create_at }}</td>
                                <td>{{ \App\Http\Utils\AppUtils::MAIL_STATE_CODE[$mail->state] }}</td>
                                <td>{{ $mail->send_times }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @include('layouts.table_footer',['data' => $mailList])
            </div>
        </div>
        <div class="card mt-3" ng-if="showMailId">
            <div class="card-header">詳細</div>
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
                            <td>宛先</td>
                            <td><% showMailDetail.to_email %></td>
                        </tr>
                        <tr class="">
                            <td>件名</td>
                            <td><% showMailDetail.subject %></td>
                        </tr>
                        <tr class="">
                            <td>本文</td>
                            <td id="detail-mail-body"></td>
                        </tr>
                    </tbody>
                </table>
                <br/>
                <div class="" ng-if="showMailDetail.state==3">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-6 col-xl-12 mb-3 text-right">
                                <button class="btn btn-success" ng-click="sendEmail(showMailDetail.id)"><i class="fas fa-envelope" ></i> 再送</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endif
    </form>
</div>


@push('scripts')
    <script>
        var hasChange = false;
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http){

                $scope.showMailId = 0;
                $scope.showMailDetail = {};

                $scope.showDetail = function(id){

                    let maillist = document.getElementsByName('mail-detail');
                    for(var i=0;i<maillist.length;i++){
                        maillist[i].style.backgroundColor = "";
                    }
                    let mail = document.getElementById('mail-detail-'+id);
                    mail.style.backgroundColor = 'rgba(0,0,0,.05)';

                    $scope.showMailId = id;

                    $http.get(link_ajax + "/" +id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.showMailDetail = event.data.showMailDetail;
                                $scope.showMailDetail.body = $scope.showMailDetail.body.replace(/(<([^>]+)>)/ig,"<br>");
                                $scope.showMailDetail.body = $scope.showMailDetail.body.replace(/\\r\\n/g,"<br>");
                                $scope.showMailDetail.body = $scope.showMailDetail.body.replace(/\n/g,"<br>");
                                $("#detail-mail-body").html($scope.showMailDetail.body);
                            }
                        });
                };

                $scope.sendEmail = function(id){

                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/send-mail/" + id)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                document.adminForm.submit();
                                $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });

                    $scope.showMailId = 0;
                };

            });

        }else{
            throw new Error("Something error init Angular.");
        }
        $("#modalDetailItem").on('hide.bs.modal', function () {
             if(hasChange){
                 location.reload();
             }
        });
    </script>
@endpush
