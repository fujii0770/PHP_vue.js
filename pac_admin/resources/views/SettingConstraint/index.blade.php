@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('postUpdateSettingCorporate') }}";
    </script>
@endpush

@section('content')
    <!-- PAC_5-413 スターターユーザーは追加予定がないため非表示
    <div class="SettingCorporate">
        <div ng-controller="SettingGeneralController">
            <div class="message"></div>

            <div class="card mt-3">
                <div class="card-header">一般利用者制限設定</div>
                <div class="card-body form-horizontal">
                    <div class="row form-group">
                        <label class="col-md-2 control-label">利用者タイプ</label>
                        <div class="col-lg-3">
                            <select class="form-control" ng-change="fetchData()" ng-model="company_id">
                                <option value="">スターター</option>
                            </select>
                        </div>
                        <div class="col-lg-7"></div>
                    </div>
                    <hr class="style-hr">
                    <div class="row form-group">
                        <label class="col-md-3 control-label">送信可能回数 <span class="star-mark">*</span></label>
                        <div class="col-lg-2">
                            <input class="form-control" type="number"  value="{{config('app.constraints_max_requests')}}">
                        </div>
                        <div class="col-lg-7">
                            <span class="form-note">一日当たりの最大送信可能回数を設定します（0は無制限）</span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">ファイルサイズ(MB) <span class="star-mark">*</span></label>
                        <div class="col-lg-2">
                            <input class="form-control" type="number" value="{{config('app.constraints_max_doccument_size')}}">
                        </div>
                        <div class="col-lg-7">
                            <span class="form-note">一度に回覧可能な最大ファイルサイズを設定します</span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">ディスク容量(MB) <span class="star-mark">*</span></label>
                        <div class="col-lg-2">
                            <input class="form-control" type="number" value="{{config('app.constraints_user_storage_size')}}">
                        </div>
                        <div class="col-lg-7">
                            <span class="form-note">ユーザ当たりの最大ディスク容量を設定します</span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">容量通知(%)<span class="star-mark">*</span></label>
                        <div class="col-lg-2">
                            <input class="form-control" type="number" value="{{config('app.constraints_use_storage_percent')}}">
                        </div>
                        <div class="col-lg-7">
                            <span class="form-note">通知を行うディスク使用率を設定します</span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">保存日数<span class="star-mark">*</span></label>
                        <div class="col-lg-2">
                            <input class="form-control" type="number" value="{{config('app.constraints_max_keep_day')}}">
                        </div>
                        <div class="col-lg-7">
                            <span class="form-note">ファイルの最大保存期間を設定します</span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">削除予告日数<span class="star-mark">*</span></label>
                        <div class="col-lg-2">
                            <input class="form-control" type="number" value="{{config('app.constraints_delete_informed_day')}}">
                        </div>
                        <div class="col-lg-7">
                            <span class="form-note">保存期間を過ぎたファイルの削除予告を行う事前日数を設定します</span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-lg-9">
                        </div>
                        <div class="col-lg-3">
                            <button class="btn btn-success m-0">更新</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

   
   
    <div class="SettingCorporate">
        <div ng-controller="SettingCorporateController">
            <form action="" class="form_constraints" onsubmit="return false;">
            <div class="message"></div>
                <div class="card mt-3">
                    <div class="card-header">企業利用者制限設定（デフォルト）</div>
                    <div class="card-body form-horizontal">
                        <div class="row form-group">
                            <label class="col-md-3 control-label">送信可能回数 <span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" ng-model="setting.requests_max" id="requests_max">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">一日当たりの最大送信可能回数を設定します（0は無制限）</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">ファイルサイズ(MB) <span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="file_size" ng-model="setting.file_size" id="file_size"  max="20">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">一度に回覧可能な最大ファイルサイズを設定します</span>
                            </div>
                        </div>
                        <!--
                        <div class="row form-group">
                            <label class="col-md-3 control-label">ディスク容量(MB) <span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="disk_capacity"  ng-model="setting.disk_capacity" id="disk_capacity" >
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">企業内で使用できるディスク容量を設定します</span>
                            </div>
                        </div>
                        -->
                        <div class="row form-group">
                            <label class="col-md-3 control-label">容量通知(%)<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="storage_percent" ng-model="setting.storage_percent" id="storage_percent" min="1" max="100">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">通知を行うディスク使用率を設定します</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">保存日数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="retention_day" ng-model="setting.retention_day" id="retention_day" >
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">ファイルの最大保存期間を設定します</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">削除予告日数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="delete_day" ng-model="setting.delete_day" id="delete_day">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">保存期間を過ぎたファイルの削除予告を行う事前日数を設定します</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">長期保管ディスク容量通知(%)<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="long_term_storage_percent" ng-model="setting.long_term_storage_percent" id="long_term_storage_percent" required
                                       min="1" max="100">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">通知を行う長期保管ディスク使用率を設定します</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">IPアドレス制限登録件数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="max_ip_address_count" ng-model="setting.max_ip_address_count" id="max_ip_address_count" required
                                       min="1">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">IPアドレス制限の登録件数上限値を設定します</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">閲覧ユーザー登録件数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="max_viwer_count" ng-model="setting.max_viwer_count" id="max_viwer_count" required
                                       min="1">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">閲覧ユーザーの登録件数上限値を設定します</span>
                            </div>
                        </div>


                        <div class="row form-group">
                            <label class="col-md-3 control-label">保有可能ダウンロード要求数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="dl_request_limit" ng-model="setting.dl_request_limit" id="dl_request_limit" required
                                       min="0" max="65535">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">ダウンロード状況確認一覧に保有出来るダウンロード要求数(0:無制限)</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">一時間当たりのダウンロード要求数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="dl_request_limit_per_one_hour" ng-model="setting.dl_request_limit_per_one_hour" id="dl_request_limit_per_one_hour" required
                                       min="0" max="65535">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">一時間当たりのダウンロード要求可能な回数数(0:無制限)</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">ダウンロード最大保存期間<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="dl_max_keep_days" ng-model="setting.dl_max_keep_days" id="dl_max_keep_days" required
                                       min="0" max="100">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">ダウンロード要求後の保存期間(単位:日)</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">ダウンロード後削除<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="dl_after_proc" ng-model="setting.dl_after_proc" id="dl_after_proc" required
                                       min="0" max="1">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">ダウンロード後の動作(0:削除、1:保存期間満了後に削除)</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">ダウンロード後保存期間<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="dl_after_keep_days" ng-model="setting.dl_after_keep_days" id="dl_after_keep_days" required
                                       min="0" max="65535">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">ダウンロード後の保存期間(単位:日) ※最大保存期間が優先</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">ダウンロードファイル容量<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="dl_file_total_size_limit" ng-model="setting.dl_file_total_size_limit" id="dl_file_total_size_limit" required
                                       min="0" max="10485760">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">ダウンロード保存総容量(単位:MB) </span>
                            </div>
                        </div>
                        @if(!config('app.pac_app_env'))
                        <div class="row form-group">
                            <label class="col-md-3 control-label">添付ファイル容量（MB）<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="max_attachment_size" ng-model="setting.max_attachment_size" id="max_attachment_size" required
                                      placeholder="0" min="1" >
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">添付ファイル機能の1ファイルあたりの最大ファイルサイズ</span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">添付ファイル合計容量（GB）<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="max_total_attachment_size" ng-model="setting.max_total_attachment_size" id="max_total_attachment_size" required
                                       placeholder="0" min="1">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">添付ファイル機能の合計の最大ファイルサイズ </span>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-md-3 control-label">添付ファイル数<span class="star-mark">*</span></label>
                            <div class="col-lg-2">
                                <input class="form-control" type="number" name="max_attachment_count" ng-model="setting.max_attachment_count" id="max_attachment_count" required
                                       placeholder="0"  min="1">
                            </div>
                            <div class="col-lg-7">
                                <span class="form-note">添付ファイル機能の最大アップロードファイル数 </span>
                            </div>
                        </div>
                        @endif
                        <div class="row form-group">
                            <div class="col-lg-9">
                            </div>
                            <div class="col-lg-3">
                                <button class="btn btn-success m-0" type="submit" ng-click="save()">更新</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>



@endsection


@push('scripts')
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('SettingGeneralController', function($scope, $rootScope, $http){ 
                
            });

            appPacAdmin.controller('SettingCorporateController', function($scope, $rootScope, $http){
                $scope.setting = {!! json_encode($envConfig) !!};
                

                $scope.save = function(){
                    if($('.form_constraints')[0].checkValidity()) {
                        hideMessages();
                        $rootScope.$emit("showLoading");
                        $http.post(link_ajax, $scope.setting)
                            .then(function(event) {
                                $rootScope.$emit("hideLoading");
                                if(event.data.status == false){
                                    $(".message").append(showMessages(event.data.message, 'danger', 10000));
                                }else{
                                    $(".message").append(showMessages(event.data.message, 'success', 10000));
                                }
                            });
                    }
                };
            });

        }else{
            throw new Error("Something error init Angular.");
        }
    </script>
@endpush



