@extends('../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ url('circulars-downloadlist') }}";
    </script>
@endpush

@section('content')
    <span class="clear"></span>
    <div ng-controller="ListController" class="list-view">
        <form action="{{\App\Http\Utils\AppUtils::forceSecureUrl(Request::url())}}" name="adminForm" method="POST">
            @csrf

            <div class="form-search form-horizontal">
                <div class="message message-list mt-3"></div>
                <div>
                    文書のダウンロードファイルの作成が完了次第、メールにて通知いたします。
                    <p>
                        ※当社ユーザ企業様において「ダウンロードファイルの作成」が大量に発生した場合は、ファイルを作成開始するまでお時間がかかる場合があります。
                    </p>
                </div>
                <div class="card mt-3">
                    <div class="card-header">ダウンロードファイル一覧</div>
                    <div class="card-body">

                    <span class="clear"></span>
                    <table class="tablesaw-list tablesaw table-bordered adminlist mt-1" data-tablesaw-mode="swipe">
                        <thead>
                            <tr>
                                <th scope="col" class="sort">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('文書名', 'R.file_name', $orderBy, $orderDir) !!}
                                </th>                                     
                                <th scope="col" class="sort" style="width: 180px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('作成日', 'R.contents_create_at', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 200px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ダウンロード期間', 'R.download_period', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 200px">
                                    {!! \App\Http\Utils\CommonUtils::showSortColumn('ダウンロード', 'R.state', $orderBy, $orderDir) !!}
                                </th>
                                <th scope="col" class="sort" style="width: 100px">
                                    削除
                                </th>                    
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itemsCircular as $i => $item)
                                <tr class="">
                                    <td>{{ $item->file_name }}</td>
                                    <td>{{ date("Y/m/d H:i:s", strtotime($item->contents_create_at)) }}</td>
                                    <td>{{ date("Y/m/d H:i:s", strtotime($item->download_period)) }}</td>
                                    <td style="text-align: center">{!! \App\Http\Utils\CommonUtils::showSortBtnColumn($item->state, $item->id, $item->sanitizing_state) !!}</td>
                                    <td style="text-align: center">{!! \App\Http\Utils\CommonUtils::showRemoveBtnColumn($item->id) !!}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    </div>
                </div>
                @include('layouts.table_footer',['data' => $itemsCircular])
            <% boxchecked %>
            <input type="hidden" value="{{ $orderBy }}" name="orderBy" />
            <input type="hidden" value="{{ Request::get('orderDir','DESC') }}" name="orderDir" />
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="action" value="">
            <input type="hidden" name="rid" value="">
            </div>
        </form>
    </div>
   
@endsection

@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ListController', function($scope, $rootScope, $http, $interval){             
                $scope.selected = [];
                // $scope.isCheckAll = false;
                $scope.cids = {!! json_encode($itemsCircular->pluck('id')) !!};

                $scope.download = function(e){ 
                    var e = e || window.event;
                    var elem = e.target || e.srcElement;
                    var elemValue = elem.value;
                    $scope.selected.push(elemValue);
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択文書ダウンロード',
                        btnSuccess:'ダウンロード',
                        size:'nomal',
                        callSuccess: function(){
                            $http.post(link_ajax+"/export", {rid: elemValue})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    const byteString = Base64.atob(event.data.file_data);
                                    const ab = new ArrayBuffer(byteString.length);
                                    const ia = new Uint8Array(ab);
                                    for (let i = 0; i < byteString.length; i++) {
                                        ia[i] = byteString.charCodeAt(i);
                                    }
                                    const dataBlob = new Blob([ab]);
                                    downloadFile(dataBlob, event.data.fileName);
                                    setTimeout(function () {
                                        window.location.reload();
                                    },30);
                                }
                            });
                        }
                    });
                 };

                 $scope.delete = function(e){
                    var e = e || window.event;
                    var elem = e.target || e.srcElement;
                    var elemValue = elem.value;
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'選択された文書を削除します。よろしいですか？', 
                        btnDanger:'削除',
                        size:'nomal',
                        callDanger: function(){
                            document.adminForm.action.value = 'delete';
                            document.adminForm.rid.value = elemValue;
                            document.adminForm.submit();
                        }
                    });
                 };

                 //PAC_5-2874 S
                $scope.sanitizingWaitUpdate = function(e){
                    var e = e || window.event;
                    var elem = e.target || e.srcElement;
                    var elemValue = elem.value;
                    $scope.selected.push(elemValue);
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'選択文書無害化',
                            btnSuccess:'無害化',
                            size:'nomal',
                            callSuccess: function(){
                                $http.post(link_ajax+"/sanitizingUpdate", {rid: elemValue})
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                            window.location.reload();
                                        }
                                    });
                            }
                        });
                };

                 $scope.rerequest = function(e){
                    var e = e || window.event;
                    var elem = e.target || e.srcElement;
                    var elemValue = elem.value;
                    $rootScope.$emit("showMocalConfirm", 
                    {
                        title:'再度ダウンロード要求をいたしますか？', 
                        btnSuccess:'はい',
                        size:'nomal',
                        callSuccess: function(){
                            $http.post(link_ajax+"/rerequest", {rid: elemValue})
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message-list").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message-list").append(showMessages(event.data.message, 'success', 10000));
                                    window.location.reload();
                                }
                            });
                        }
                    });
                 };

                 // 10分毎に更新
                 var t = $interval(function(){
                    window.location.reload();
                 }, {{config('app.reload_interval')}});
            });
        }
    </script>
@endpush
