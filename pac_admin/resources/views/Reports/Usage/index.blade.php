@extends('../../layouts.main')

@push('scripts')
    <script>
        var appPacAdmin = initAngularApp(),
        link_ajax = "{{ route('Reports.Usage.Show') }}";
        link_ajax_stamp_register_state = "{{ route('CsvStampRegisterState') }}";
        link_ajax_stamp_ledger = "{{ route('CsvStampLedger') }}";
        link_ajax_csv2 = "{{ route('CsvUserRegistrationStatus') }}";
        link_ajax_disk_usage = "{{ route('CsvUserDiskUsages') }}";
        link_ajax_disk_host_usage = "{{ route('CsvUserDiskHostUsages') }}";
    </script>
    {{--PAC_5-2289 S--}}
    <script src="{{ asset('/js/monthpicker.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/css/monthpicker.css') }}">
    {{--PAC_5-2289 E--}}
@endpush

@section('content')

    <span class="clear"></span>
    <div class="Reports Reports-usage">
        <div ng-controller="ReportsUsageController">
            <div class="message"></div>
            <div class="row">
                <div class="col-lg-9 col-md-8"><span style="padding: .75rem 1.25rem">利用状況　【集計日：{{ date('Y/m/d',strtotime('-1 day')) }}】</span></div>
                <div class="col-lg-3 col-md-4 d-flex d-inline-flex space">
                    @if(!$check_role_shachihata)
                       {{-- <a class="px-2" href="{{ config('app.help_url_first_time_set') }}" target="_blank">初回設定はこちら</a>
                        <a class="px-2" href="{{ config('app.help_url_user_registration') }}" target="_blank">利用者登録はこちら</a>
                        <a class="px-2" href="{{ config('app.help_url_common_mark_setting') }}" target="_blank">共通印設定はこちら</a>--}}
                    @endif
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">利用者・印面登録状況</div>
                <div class="card-body form-horizontal">
            　      @hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
                        <div class="row form-group">
                        <label for="yearmonth" class="col-md-3 control-label">対象企業</label>
                        <div class="col-lg-4">
                        <select class="form-control select2" id="company_id" ng-change="fetchData()" ng-model="company_id">
                            <option value="">全企業</option>
                            @for($i=0; $i<count($list_company); $i++)
                                <option value="{{ $list_company[$i]->id }}" is_guest="{{$list_company[$i]->guest_company_flg}}">{{ $list_company[$i]->guest_company_flg?$list_company[$i]->company_name.'（ゲスト）':$list_company[$i]->company_name }}</option>
                            @endfor
                        </select>
                    </div>
                        <div class="col-lg-5"></div>
                    </div>
                    @endhasrole
                    @if(!$check_role_shachihata && $is_host_company)
                         <div class="row form-group">
                        <label for="yearmonth" class="col-md-3 control-label">対象企業</label>
                        <div class="col-lg-4">
                            <select class="form-control select2" id="company_id" ng-change="fetchHostCompanyData()" ng-model="company_id">
                                <option value="">全企業</option>
                                <option value="{{$host_company}}">{{$host_company_name}}</option>
                                @for($i=0; $i<count($list_guest_company); $i++)
                                    <option value="{{ $list_guest_company[$i]->guest_company_id }}">{{ $list_guest_company[$i]->guest_company_name.'（ゲスト）' }}</option>
                                @endfor
                                <option value="-1">ゲスト企業の合計</option>
                            </select>
                        </div>
                        <div class="col-lg-5"></div>
                    </div>
                    @endif

                    <div class="row form-group">
                        <label for="yearmonth" class="col-md-3 control-label">対象月</label>
                        <div class="col-lg-4">
                            <select class="form-control" ng-change="{{$is_host_company == true ? 1 : 0}} ? fetchHostCompanyData() : fetchData()" ng-model="yearmonth">
                                <option value="{{ date('Y,m') }}">今月({{ date('Y/m') }})</option>
                                @for($i=1; $i<13; $i++)
                                <option value="{{Carbon\Carbon::now()->subMonthsWithoutOverflow($i)->format("Y,m")}}">{{ $i }}ヵ月前({{Carbon\Carbon::now()->subMonthsWithoutOverflow($i)->format("Y/m")}})</option>
                                    @endfor
                            </select>
                        </div>
                        <div class="col-lg-5"></div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">当月有効利用者総数</label><!--PAC_5-2522 当月利用者総数→当月有効利用者総数変更-->
                        <div class="col-lg-6" style="line-height: 38px;">
                             <span ng-bind-html="(info.user_total_count) + ' / ' + (info.total_contract_count > 0 ? info.total_contract_count : '-')"></span>人
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">割当中の印面の合計</label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            <span ng-bind-html="info.total_name_stamp + info.total_date_stamp + info.total_common_stamp"></span>
                             個（
                                 氏名印: <span ng-bind-html="info.total_name_stamp"></span><span class="pr-3">個、</span>
                                  日付印: <span ng-bind-html="info.total_date_stamp"></span><span class="pr-3">個、</span>
                                  共通印: <span ng-bind-html="info.total_common_stamp"></span>個
                                ）
                            / <span ng-bind-html="header_info.stamp_contract"></span>個
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group"  >
                        <label class="col-md-3 control-label"></label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            印面利用者ライセンス数： <span ng-bind-html="info.user_total_count"></span> 個<!--PAC_5-2522 ユーザーライセンス印面数→印面利用者ライセンス数-->
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group"  >
                        <label class="col-md-3 control-label"></label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            印面追加ライセンス数：<span ng-bind-html="info.total_name_stamp + info.total_date_stamp + info.total_common_stamp-info.user_total_count>=0?(info.total_name_stamp + info.total_date_stamp + info.total_common_stamp-info.user_total_count):0"></span>個<!--PAC_5-2522 追加印面数→印面追加ライセンス数-->
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group"  >
                        <label class="col-md-3 control-label"></label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            未使用印面ライセンス数：<span ng-bind-html="header_info.stamp_contract - (info.total_name_stamp + info.total_date_stamp + info.total_common_stamp)"></span>個<!--PAC_5-2522 残ライセンス数→未使用印面ライセンス数-->
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            ※「当月有効利用者総数」、「割当中の印面の合計」は <%getDate()%> 分を表示<!--PAC_5-2522 当月利用者総数→当月有効利用者総数変更-->
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">便利印割当数</label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            <span ng-bind-html="info.total_convenient_stamp"></span> / <span ng-bind-html="info.convenient_upper_limit"></span> 個
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">オプション利用数</label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            <span ng-bind-html="info.total_option_contract_count > 0 ? info.user_total_count : '-'"></span> / <span ng-bind-html="info.total_option_contract_count > 0 ? info.total_option_contract_count : '-'"></span> 人
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group">
                    <label class="col-md-3 control-label">タイムスタンプ発行数</label>
                    <div class="col-lg-6" style="line-height: 38px;">
                        <span ng-bind-html="info.total_time_stamp"></span> / <span ng-bind-html="info.timestamps_count > 0 ? info.timestamps_count : '-'"></span> 個
                    </div>
                    <div class="col-lg-3"></div>
                    </div>
                    @if ($show_longterm_storage)
                        <div class="row form-group">
                            <label class="col-md-3 control-label">長期保管ディスク使用容量</label>
                            <div class="col-lg-6" style="line-height: 38px;" ng-bind-html="info.storage_use_capacity">
                            </div>
                            <div class="col-lg-3"></div>
                        </div>
                    @endif
                    <div class="row form-group">
                        <label class="col-md-3 control-label">ゲストユーザー数</label>
                        <div class="col-lg-6" style="line-height: 38px;">
                            <span ng-bind-html="info.guest_user_total_count"></span>人（同一ドメイン：<span ng-bind-html="info.same_domain_number"></span>人）
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                    <div class="row form-group">
                        <label class="col-md-3 control-label">利用容量</label>
                        <div class="col-lg-4" style="line-height: 38px;display: flex;align-items: center">
                            <div style="width: 40%">
                                <span ng-bind-html="header_info.storage_sum_re"></span> / <span ng-bind-html="header_info.user_count_valid * 1024"></span> MB
                            </div>
                            <div class="progress" style="width: 60%">
                                <div class="progress-bar" role="progressbar" aria-valuenow=" <% header_info.storage_rate %>" aria-valuemin="0" aria-valuemax="100" style="min-width: 0.1em;width:<% header_info.storage_rate + '%' %>"></div>
                            </div>
                        </div>
                        <div class="col-lg-2" >
                            <button id="reSituationBtn" class="btn btn-success " ng-click="reSituation()">再集計</button>
                        </div>

                    </div>
                    @if(!$check_role_shachihata && $sanitizing_flg == false)
                    <div class="row">
                        <div style="width:100%;text-align: right;">
                            <button type="button" class="btn btn-warning mb-1" ng-click="downloadCsvStampRegisterState({{$is_host_company}})"><i class="fas fa-download"></i> CSV出力</button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)
            <div class="card mt-3" id="history-csv" style="display: none">
                <div class="card-header">操作履歴CSV出力</div>
                <div class="card-body form-horizontal">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-2">
                            {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('from_date','対象期間From',Request::get('from_date', ''),'date', false,
                            [ 'placeholder' =>'対象期間From', 'id'=>'from_date' ]) !!}
                        </div>
                        <div class="col-md-2">
                            {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('to_date','対象期間To',Request::get('to_date', ''),'date', false,
                            [ 'placeholder' =>'対象期間To', 'id'=>'to_date' ]) !!}
                        </div>
                        <div class="col-md-5"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-9">
                            <div class="btn btn-warning  mb-1" ng-click="downloadHisAdmin()"><i class="fas fa-download" ></i> 管理者</div>
                            <div class="btn btn-warning  mb-1" ng-click="downloadHisUser()"><i class="fas fa-download" ></i> 利用者</div>
                            <input type="hidden" class="action" name="action" value="" />
                        </div>
                    </div>
                </div>
            </div>
            @endhasrole

            @if(!$check_role_shachihata)
            <div>
                <div class="row">
                    <div class="col-lg-9 col-md-8"><span style="padding: .75rem 1.25rem">利用分析　【集計日：{{ date('Y/m/d',strtotime('-1 day')) }}】</span></div>
                </div>
                <form class="form-horizontal" role="form">
                    <div class="form-group row">
                        <div class="col-sm-2 col-md-2 col-lg-7"></div>
                        <label class="col-sm-3 col-md-3 col-lg-2 control-label">対象期間</label>
                        <div class="col-sm-4 col-md-4 col-lg-2" style="line-height: 40px;">
                            <label class="radio-inline"><input type="radio" name="target-month" value="1" ng-checked="range==1" />１ヶ月</label>
                            <label class="radio-inline"><input type="radio" name="target-month" value="3" ng-checked="range==3" />３ヶ月</label>
                            <label class="radio-inline"><input type="radio" name="target-month" value="6" ng-checked="range==6" />６ヶ月</label>
                        </div>
                        <div class="col-sm-3 col-md-3 col-lg-1">
                            <button class="btn btn-primary mb-1" id="search-report" ng-click="{{$is_host_company ? 'showHostCompanyChart()' : 'showChart()'}}"><i class="fas fa-search" ></i> 検索</button>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">ファイル容量が多い利用者</div>
                            <div class="card-body form-horizontal">
                                <div class="message_diskUsagesCsv"></div>
                                <div id="disk-usage-nodata" style="width:100%;height:550px;">データが無いためグラフは表示されません </div>
                                <div id="disk-usage-chart" style="width:100%;height:550px;"></div>
                                <div class="row">
                                    <div style="width:100%;text-align: right; padding-right:80px">
                                        <button type="button" class="btn btn-warning mb-1 margin-right-10" ng-click="{{$is_host_company?'downHostDiskUsages()':'downDiskUsages()'}}"><i class="fas fa-download"></i> CSV出力 </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">月別申請件数</div>
                            <div class="card-body form-horizontal">
                                <div id="month-requests-nodata" style="width:100%;height:550px;">データが無いためグラフは表示されません </div>
                                <div id="month-requests-chart" style="width:100%;height:590px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mt-3" id="stamp-csv" style="height:470px;">
                            <div class="card-header">捺印台帳CSV出力</div>
                            <div class="card-body form-horizontal">
                                <div class="message_stampcsv"></div>
                                <div class="row">
                                    <!-- <div class="col-md-4">
                                    </div> -->
                                    <div class="col-md-4">
                                        {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('select_month','対象月',Request::get('select_month', ''),'month', true,
                                        [ 'placeholder' =>'対象月', 'id'=>'select_month', 'ng-focus'=>'timeFocus();']) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('s_name','印鑑シリアル',Request::get('s_serial', ''),'text', false,
                                        [ 'placeholder' =>'印鑑シリアル', 'id'=>'s_serial']) !!}
                                    </div>
                                    <div style="padding-top:36px">
                                        <div class="btn btn-warning  mb-1" ng-click="downloadCsvStampLedger()"><i class="fas fa-download" ></i> CSV出力</div>
                                        <input type="hidden" class="action" name="action" value="" />
                                    </div>

                                </div>
                            </div>
                            <div style="font-weight: bold;margin-left:1em;">印鑑プレビュー</div>
                            <div class="card-body form-horizontal">
                                <div class="preview_stamp"></div>
                                <div class="row">
                                    <div class="col-md-4">
                                        {!! \App\Http\Utils\CommonUtils::showFormFieldVertical('name','印鑑シリアル',Request::get('serial', ''),'text', false,
                                        [ 'placeholder' =>'印鑑シリアル', 'id'=>'serial']) !!}
                                    </div>
                                    <div  style="padding-top:36px">
                                        <button class="btn btn-primary mb-1" ng-click="stampPreview()" id="search-stamp" ><i class="fas fa-search" ></i> プレビュー</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- <div class="col-md-12" ng-if="previewStamp.stamp_image"> -->
                                    <div class="col-md-12">
                                        <div class="thumb">
                                            <div style=" display: inline-block">
                                                <label style="font-weight: bold;width:120px">メールアドレス</label><span ng-bind-html="previewStamp.email"></span></br>
                                                <label style="font-weight: bold;width:120px">氏名</label><span ng-bind-html="previewStamp.name"></span>
                                            </div>
                                            <div class="thumb-img" style="height: 90px; max-width: 100%; border:solid 1px #e5e5e5">
                                                <img style="max-height: 85px; max-width: 100%" ng-src="data:image/png;base64,<% previewStamp.stamp_image %>" class="stamp-image" />
                                            </div>

                                            <!-- <img ng-src="data:image/png;base64,<% stamp.stamp_master.stamp_image %>" class="stamp-image" /> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card mt-3" id="preview" style="height:470px;">
                            <div class="card-header">登録状況CSV出力</div>
                            <div class="card-body form-horizontal">
                        <!--    <div class="message message-list mt-3"></div> -->
                                <div class="registration_csv"></div>
                                <div class="row">
                                    <div style="padding-top:36px">
                                        <label style="font-weight: bold;margin-left:1em;">ユーザー印面割り当て状況</label>
                                        <div class="btn btn-warning  mb-1" ng-click="downloadCsvRegistration()"><i class="fas fa-download" ></i> CSV出力</div>
                                        <input type="hidden" class="action" name="action" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div id="statisticsDiv">
                <div class="row">
                    <div class="col-3" >
                        <div class="block">
                            <div class="title">ユーザー</div>
                            <div style="display: flex;">
                                <div class="number" style="width: 10%" ng-bind-html="header_info.user_count_valid"></div>
                                <div style="width: 90%;height: 60px;" id="userChart">
                                </div>
                            </div>
                            <div>
                                <img class="icon"  ng-if="userUpDownFlag" src="{{ asset('images/arrow-up-green.png') }}">
                                <img class="icon"  ng-if="!userUpDownFlag" src="{{ asset('images/arrow-down.png') }}">

                                <span style="color: #14881c" id="user_rate">
                                            --
                                    </span>
                                <span>
                                            過去30日
                                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="block">
                            <div class="title">ストレージ</div>
                            <div style="display: flex">
                                <div  style="width: 40%">
                                        <span class="number">
                                            <span ng-bind-html="header_info.storage_sum_re"></span> <span style="font-size: 20px" ng-bind-html="'/ '+header_info.user_count_valid * 1024"></span>
                                        </span>
                                    <span>mb</span>
                                </div>
                                <div style="width: 60%;height: 60px;" id="storageChart">
                                </div>
                            </div>
                            <div>
                                <img class="icon"  ng-if="storageUpDownFlag" src="{{ asset('images/arrow-up-green.png') }}">
                                <img class="icon"  ng-if="!storageUpDownFlag" src="{{ asset('images/arrow-down.png') }}">

                                <span style="color: #14881c" id="rate">
                                            --
                                    </span>
                                <span>
                                            過去30日
                                    </span>
                            </div>
                            <div>
                                <a class="link" href="https://dstmp-order.shachihata.com/mypage/" target="_blank">追加のストレージを購入</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="block">
                            <div class="title">
                                <span>登録総印鑑数</span> <a href="" style="margin-left: 30px;color: darkred"><span ng-bind-html="header_info.stamp_over_count"></span>ライセンス超過</a>
                            </div>
                            <div style="display: flex">
                                <div class="number" style="width: 55%;">
                                    <span ng-bind-html="header_info.stamp_count"></span>  <span style="font-size: 20px" ng-bind-html="'/ '+ header_info.stamp_contract"> </span>
                                </div>
                                <div style="width: 90%;height: 60px;" id="stampChart">
                                </div>
                            </div>
                            <div>
                                <img class="icon"  ng-if="stampUpDownFlag" src="{{ asset('images/arrow-up-green.png') }}">
                                <img class="icon"  ng-if="!stampUpDownFlag" src="{{ asset('images/arrow-down.png') }}">

                                <span style="color: #14881c" id="stamp_rate">
                                            --
                                    </span>
                                <span>
                                            過去30日
                                    </span>
                            </div>
                            <div><a class="link" href="https://dstmp-order.shachihata.com/mypage/" target="_blank">追加購入はコチラから</a></div>
                        </div>

                    </div>
                    <div class="col-3">
                        <div class="block">
                            <div class="title">
                                <span>タイムスタンプ使用回数</span>
                                <a href="" ng-if="header_info.timestamp_leftover_count >= 0" style="margin-left: 20px;color: green">残り<span ng-bind-html="header_info.timestamp_leftover_count"></span>回</a>
                                <a href="" ng-if="header_info.timestamp_leftover_count < 0" style="margin-left: 20px;color: darkred"><span ng-bind-html="header_info.timestamp_leftover_count * -1"></span>ライセンス超過</a>
                            </div>
                            <div style="display:flex;">
                                <div class="number" style="width: 35%;">
                                    <span ng-bind-html="header_info.timestamp_count"></span> <span style="font-size: 20px"> / <span ng-bind-html="info.timestamps_count > 0 ? info.timestamps_count : '-'"></span> </span></div>
                                <div style="width: 65%;height: 60px;" id="timestampChart">

                                </div>
                            </div>
                            <div>
                                <img class="icon"  ng-if="timestampUpDownFlag" src="{{ asset('images/arrow-up-green.png') }}">
                                <img class="icon"  ng-if="!timestampUpDownFlag" src="{{ asset('images/arrow-down.png') }}">

                                <span style="color: #14881c" id="timestamp_rate">
                                            --
                                    </span>
                                <span>
                                            過去30日
                                    </span>
                            </div>
                            <div><a class="link" href="https://dstmp-order.shachihata.com/mypage/" target="_blank">追加購入はコチラから</a></div>
                        </div>

                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header" style="display: flex;justify-content: space-between;align-content: center">
                        <div id="title">使用状况</div>
                        <div style="font-size: 14px">
                            <img class="icon" src="{{ asset('images/download.png') }}"
                                 style="cursor: pointer"
                                 ng-click="{{$is_host_company ? 'downloadHostSummaryData()' : 'downloadSummaryData()'}}"
                            >
                        </div>

                    </div>
                    <div class="row" style="padding: 10px">
                        <div class="col-2">
                            <select class="form-control" ng-change="fetchData()" ng-model="statistics_range">
                                @foreach(\App\Http\Utils\AppUtils::USAGE_KIKAN as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-3">
                            <select class="form-control" ng-change="reloadChart()" id="statistics_type" ng-model="statistics_type">
                                <option ng-repeat="(k,v) in shuukei_koumoku" ng-value="k"  ng-bind-html="v.name"> </option>
                            </select>
                        </div>

                    </div>
                    <div class="row" style="margin: 0; padding-left: 10px">
                        <div class="card mini-card"  style="padding: 0;margin-left: 0px; height: 200px; margin-top: 20px; max-width: 12%!important;width: 12%!important;">
                            <div class="card-header" style="display: flex;justify-content: center;align-content: center" ng-bind-html="shuukei_koumoku[statistics_type].name">
                            </div>
                            <div class="card-body">
                                <div class="form-check" ng-repeat="(k,v) in shuukei_koumoku[statistics_type].child">
                                    <input class="form-check-input" type="checkbox" ng-model="v.checked" ng-change="miniCardChange(v,true)" id="useage_<%k%>"  >
                                    <label class="form-check-label" for="useage_<%k%>" ng-bind-html="v.name">

                                    </label>
                                </div>
                            </div>
                        </div>
                        <div id="statisticsChart" class="" style="height:500px; width: 87%; padding-left: 20px  "></div>
                    </div>

                </div>

                <div class="card mt-3">
                    <div class="card-header" style="display: flex;justify-content: space-between;align-content: center">
                        <div>最上位のファイルの種類</div>
                        <div style="font-size: 14px">過去90日間
                            <img class="icon" src="{{ asset('images/download.png') }}"
                                 style="cursor: pointer"
                                 ng-click="{{$is_host_company ? 'downloadHostFileData()' : 'downloadFileData()'}}"
                            >
                        </div>

                    </div>
                    <div class="card-body form-horizontal">
                        <div>
                            <span style="margin-right: 10px"><span class="dot orange"></span>PDF</span>
                            <span style="margin-right: 10px"><span class="dot dark-blue"></span>EXCEL</span>
                            <span style="margin-right: 10px"><span class="dot green"></span>WORD</span>

                        </div>
                        <div style="padding-top: 10px">
                            アップロード
                        </div>
                        <div id="uploadArea" style="display: flex;padding-top: 5px;width: 100%" >
                            <div id="pdfArea" title="" style="height: 5px;cursor: pointer;" class="orange"></div>
                            <div id="excelArea" title="" style="height: 5px;cursor: pointer;" class="dark-blue"></div>
                            <div id="wordArea" title="" style="height: 5px;cursor: pointer;" class="green"></div>
                        </div>
                        <div style="padding-top: 10px">
                            ダウンロード
                        </div>
                        <div style="display: flex;padding-top: 5px">
                            <div id="downloadPdfArea"style="height: 5px;cursor: pointer;border-radius: 10%;" class="orange"></div>
                        </div>

                    </div>

                </div>
            </div>
    </div>

@endsection


@push('scripts')
    <script src="{{ asset('/js/libs/echarts/3.7.0/echarts.min.js') }}"></script>
    <script>
        if(appPacAdmin){
            appPacAdmin.controller('ReportsUsageController', function($scope, $rootScope, $http){
                $scope.storageUpDownFlag = true;
                $scope.userUpDownFlag = true;
                $scope.stampUpDownFlag = true;
                $scope.timestampUpDownFlag = true;
                $scope.yearmonth = "{{ request("year", date('Y')) }},{{ request("month", date('m')) }}";
                $scope.range = "{{ request("range", 6) }}";
                $scope.statistics_range =  "{{ request("statistics_range", 0) }}";
                $scope.statistics_type =  parseInt("{{ request("statistics_type", 0) }}");
                $scope.info = {!! json_encode($data) !!};
                $scope.previewStamp = {name: "", email:"", image:""};
                $scope.header_info = {!! json_encode($summary_data) !!};
                $scope.is_host_company = "{{$is_host_company}}";
                $scope.shuukei_koumoku = {!! json_encode(\App\Http\Utils\AppUtils::SHUUKEI_KOUMOKU) !!};
                $scope.chart=null
                var role_shachihata = "{{$check_role_shachihata}}";
                initData();
                @if(isset($company_id))
                    $scope.current_company_id = "{{ $company_id }}";
                    $scope.current_company_name = "{{ $company_name }}";
                @endif

                @if(isset($mst_company_id))
                    $scope.company_id = "{{ $mst_company_id }}";
                @elseif(isset($host_company) && $is_host_company)
                    $scope.company_id = "{{ $host_company }}";
                @endif


                @if(isset($host_company) && $is_host_company)
                    $scope.host_company = "{{ $host_company }}";
                    $scope.usage_flg = "{{ $host_company_usage_flg }}";
                 @elseif(isset($company_id) && !$is_host_company)
                    $scope.usage_flg = "{{ $company_usage_flg }}";
                 @endif
                // 初期表示
                @if(!$check_role_shachihata)
                    let info_chart = {!! json_encode($data_chart) !!};
                    chart(info_chart);
                @endif

                // history-csv init
                if($scope.company_id){
                    $('#history-csv').show();
                }else {
                    $('#history-csv').hide();
                }
                $scope.intStampIsOver = '{{$intStampIsOver}}'
                $scope.$watch('$viewContentLoaded', function() {
                    if($scope.intStampIsOver == 1){
                        $rootScope.$emit("showMocalAlert",
                        {
                            size:'md',
                            title:"警告",
                            message:'{!!$stringStampIsOverMessage!!}',
                        });
                    }
                });
                $scope.miniCardChange=function (val,update){
                    $scope.reloadChart()
                };

                $scope.reloadChart=function (){
                    // 統計
                    const text = $("#statistics_type option:selected").text();
                    $('#title').html(text);

                    if ([0,1,2].includes($scope.statistics_type)){
                        $('.mini-card').fadeIn('slow')
                        $('#statisticsChart').css('width','87%')
                    }else{
                        $('.mini-card').hide()
                        $('#statisticsChart').css('width','99%')
                    }

                    statisticsChart($scope.header_info.statistics_info);
                    $scope.chart.resize()
                }

                if(role_shachihata === '1'){
                    $('#statisticsDiv').show();
                    if($scope.company_id){
                        $('#reSituationBtn').show();
                    }else{
                        $('#reSituationBtn').hide();
                    }
                    $('.link').show();
                }else{
                    if($scope.usage_flg === '1'){
                        if($scope.company_id || $scope.current_company_id){
                            $('#reSituationBtn').show();
                        }else{
                            $('#reSituationBtn').hide();
                        }
                        $('#statisticsDiv').show();
                        $('.link').show();
                    }else{
                        $('#reSituationBtn').hide();
                        $('#statisticsDiv').hide();
                        $('#history-csv').hide();
                        $('.link').hide();
                    }
                }

                $scope.fetchData = function(){
                    // history-csv
                    if($scope.company_id){
                        $('#history-csv').show();
                    }else{
                        $('#history-csv').hide();
                    }
                    if(role_shachihata === '1'){
                        if($scope.company_id){
                            $('#reSituationBtn').show();
                        }else{
                            $('#reSituationBtn').hide();
                        }
                        $('#statisticsDiv').show();
                        $('.link').show();
                    }else{
                        if($scope.usage_flg === '1'){

                            if($scope.company_id || $scope.current_company_id){

                                $('#reSituationBtn').show();
                            }else{
                                $('#reSituationBtn').hide();
                            }
                            $('#statisticsDiv').show();
                            $('.link').show();
                        }else{
                            $('#reSituationBtn').hide();
                            $('#statisticsDiv').hide();
                            $('.link').hide();
                        }
                    }

                    let yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    let newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range+"&statistics_range="+$scope.statistics_range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                        window.history.pushState({}, '', decodeURIComponent(newUrl));

                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/search/"+yearmonth[0]+"/"+yearmonth[1] +($scope.statistics_range?"/"+$scope.statistics_range:"")+ ($scope.company_id?"/"+$scope.company_id:"") )
                    .then(function(event) {
                        $rootScope.$emit("hideLoading");
                        if(event.data.status == false){
                            $(".message").append(showMessages(event.data.message, 'danger', 10000));
                        }else{
                            $scope.info = event.data.info;
                            $scope.header_info = event.data.summary_info;

                            initData();
                        }
                    });

                };

                $scope.reSituation= function (){
                    $rootScope.$emit("showLoading");
                    const companyId = $scope.company_id || $scope.current_company_id;
                    if(companyId){
                        $http.get(link_ajax + "/reSituation/"+companyId).then(function(event){
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.fetchData();
                            }
                        })

                    }
                };
                $scope.getDate=function(){
                    let yearmonth = $scope.yearmonth.split(",");
                    let now=new Date();
                    if (now.getFullYear()==yearmonth[0]&&now.getMonth()+1==yearmonth[1]){
                        return '{{date('Y/m/d',strtotime('-1 day'))}}'
                    }else{
                        let date=new Date(yearmonth[0],yearmonth[1],0)
                        let month=(date.getMonth()+1)>9?(date.getMonth()+1):'0'+(date.getMonth()+1)
                        return date.getFullYear()+'/'+month+'/'+date.getDate()
                    }
                };
                $scope.fetchHostCompanyData = function(){
                    if($scope.usage_flg === '1'){
                        if($scope.host_company == $scope.company_id){
                            $('#reSituationBtn').show();
                        }else{
                            $('#reSituationBtn').hide();
                        }
                        $('#statisticsDiv').show();

                    }else{
                        $('#reSituationBtn').hide();
                        $('#statisticsDiv').hide();
                    }

                    let yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    let newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/hostCompany/"+yearmonth[0]+"/"+yearmonth[1] +($scope.statistics_range?"/"+$scope.statistics_range:"")+ ($scope.company_id?"/"+$scope.company_id:"")+ (($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"/guest"))
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $scope.info = event.data.info;
                                $scope.header_info = event.data.summary_info;
                                initData();
                            }
                        });

                };

                $scope.downloadCsvStampRegisterState = function(is_host_company){
                    $rootScope.$emit("showLoading");
                    $http.post(link_ajax_stamp_register_state, { company_id: $scope.company_id, is_host: is_host_company })
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                $(".message").append(showMessages(event.data.message, 'success', 10000));
                            }
                        });
                };

                $scope.downloadData = function(){
                    const yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    let newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));

                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/download" + ($scope.company_id?"/"+$scope.company_id:"") )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                let infos = event.data.info;

                                // title　PAC_5-2522 当月利用者総数→当月有効利用者総数変更
                                let title = ['対象月','当月有効利用者総数','割当中の氏名印','割当中の日付印','割当中の共通印','割当中の印面の合計','タイムスタンプ発行数'];
                                @if($show_longterm_storage)
                                    title = ['対象月','当月有効利用者総数','割当中の氏名印','割当中の日付印','割当中の共通印','割当中の印面の合計','タイムスタンプ発行数','長期保管ディスク使用容量'];
                                        @endif
                                let outText = '\uFEFF';
                                for (let i = 0; i < title.length; i++) {
                                    if(i == 0){
                                        outText += title[i];
                                    } else {
                                        outText +=  ','+ title[i];
                                    }
                                }
                                for (var _i = 0, items = infos; _i < infos.length; _i++) {
                                    outText += "\r\n";
                                    outText += items[_i].target
                                        + ',' + items[_i].user_total_count
                                        + ',' + items[_i].total_name_stamp
                                        + ',' + items[_i].total_date_stamp
                                        + ',' + items[_i].total_common_stamp
                                        + ',' + (items[_i].total_name_stamp + items[_i].total_date_stamp + items[_i].total_common_stamp)
                                        + ',' + items[_i].total_time_stamp;
                                    @if($show_longterm_storage)
                                        outText += ',' + items[_i].storage_use_capacity;
                                    @endif
                                }
                                outText += "\r\n";
                                let blob = new Blob([outText], { type: "text/csv" });
                                let filename = "利用者・印面登録状況.csv";
                                if (window.navigator.msSaveBlob) {
                                    window.navigator.msSaveBlob(blob, filename);
                                }
                                else {
                                    let a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                }
                            }
                        });

                };
                $scope.downloadHostCompanyData = function(){
                    let yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    let newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/downloadGuestCompanyInfo" + ($scope.company_id?"/"+$scope.company_id:"")+ (($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"/guest"))
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                let infos = event.data.info;

                                // title　PAC_5-2522 当月利用者総数→当月有効利用者総数変更
                                let title = ['対象月','当月有効利用者総数','割当中の氏名印','割当中の日付印','割当中の共通印','割当中の印面の合計','タイムスタンプ発行数'];
                                @if($show_longterm_storage)
                                    title = ['対象月','当月有効利用者総数','割当中の氏名印','割当中の日付印','割当中の共通印','割当中の印面の合計','タイムスタンプ発行数','長期保管ディスク使用容量'];
                                        @endif
                                let outText = '\uFEFF';
                                for (let i = 0; i < title.length; i++) {
                                    if(i == 0){
                                        outText += title[i];
                                    } else {
                                        outText +=  ','+ title[i];
                                    }
                                }
                                for (var _i = 0, items = infos; _i < infos.length; _i++) {
                                    outText += "\r\n";
                                    outText += items[_i].target
                                        + ',' + items[_i].user_total_count
                                        + ',' + items[_i].total_name_stamp
                                        + ',' + items[_i].total_date_stamp
                                        + ',' + items[_i].total_common_stamp
                                        + ',' + (items[_i].total_name_stamp + items[_i].total_date_stamp + items[_i].total_common_stamp)
                                        + ',' + items[_i].total_time_stamp;
                                    @if($show_longterm_storage)
                                        outText += ',' + items[_i].storage_use_capacity;
                                    @endif
                                }
                                outText += "\r\n";
                                let blob = new Blob([outText], { type: "text/csv" });
                                let filename = "利用者・印面登録状況.csv";
                                if (window.navigator.msSaveBlob) {
                                    window.navigator.msSaveBlob(blob, filename);
                                }
                                else {
                                    let a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                }
                            }
                        });
                };
                $scope.downloadFileData = function(){
                    let yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    let newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));

                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/downloadFileInfo" + ($scope.company_id?"/"+$scope.company_id:"") )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                let infos = event.data.info;
                                let title = ['対象日','PDFアップロード数','EXCELアップロード数','WORDアップロード数','ダウンロード数'];
                                let outText = '\uFEFF';
                                for (let i = 0; i < title.length; i++) {
                                    if(i == 0){
                                        outText += title[i];
                                    } else {
                                        outText +=  ','+ title[i];
                                    }
                                }
                                for (var _i = 0, items = infos; _i < infos.length; _i++) {
                                    outText += "\r\n";
                                    outText += items[_i].target_date
                                        + ',' + items[_i].upload_count_pdf
                                        + ',' + items[_i].upload_count_excel
                                        + ',' + items[_i].upload_count_word
                                        + ',' + items[_i].download_count_pdf;
                                }
                                outText += "\r\n";
                                let blob = new Blob([outText], { type: "text/csv" });
                                let filename = "ファイルの種類状況.csv";
                                if (window.navigator.msSaveBlob) {
                                    window.navigator.msSaveBlob(blob, filename);
                                }
                                else {
                                    let a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                }
                            }
                        });

                };
                $scope.downloadHostFileData = function(){
                    let yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    let newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/downloadGuestFileInfo" + ($scope.company_id?"/"+$scope.company_id:"")+ (($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"/guest"))
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                let infos = event.data.info;
                                let title = ['対象日','PDFアップロード数','EXCELアップロード数','WORDアップロード数','ダウンロード数'];
                                let outText = '\uFEFF';
                                for (let i = 0; i < title.length; i++) {
                                    if(i == 0){
                                        outText += title[i];
                                    } else {
                                        outText +=  ','+ title[i];
                                    }
                                }
                                for (var _i = 0, items = infos; _i < infos.length; _i++) {
                                    outText += "\r\n";
                                    outText += items[_i].target_date
                                        + ',' + items[_i].upload_count_pdf
                                        + ',' + items[_i].upload_count_excel
                                        + ',' + items[_i].upload_count_word
                                        + ',' + items[_i].download_count_pdf;
                                }
                                outText += "\r\n";
                                let blob = new Blob([outText], { type: "text/csv" });
                                let filename = "ファイルの種類状況.csv";
                                if (window.navigator.msSaveBlob) {
                                    window.navigator.msSaveBlob(blob, filename);
                                }
                                else {
                                    let a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                }
                            }
                        });
                };
                $scope.downloadSummaryData = function(){

                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/downloadSummaryInfo/"+$scope.statistics_range + ($scope.company_id?"/"+$scope.company_id:"") )
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                const infos = event.data.info;
                                const sumInfos = event.data.sum_info;
                                let title = ['企業ID','企業名','対象日','契約数','印面登録数','有効ユーザ数','アクティビティユーザ数','申請数','回覧完了数','平均時間（回覧開始～終了）（h）','社外経由数（送信）','社外経由数（受信）','回覧完了率','利用率（アクティビティユーザ数／有効ユーザ数）','超過登録印面数'];
                                let outText = '\uFEFF';
                                for (let i = 0; i < title.length; i++) {
                                    if(i == 0){
                                        outText += title[i];
                                    } else {
                                        outText +=  ','+ title[i];
                                    }
                                }

                                let company_id = '-';
                                let company_name = '-';
                                if($('#company_id').length > 0 ){

                                    if($('#company_id').val() !== '' && $('#company_id').val() !== '-1'){
                                        company_id = $('#company_id').val();
                                        company_name =  $('#company_id option:selected').text();
                                    }

                                }else{
                                    company_id = $scope.current_company_id;
                                    company_name = $scope.current_company_name;
                                }
                                if(Array.isArray(sumInfos)){
                                    sumInfos.forEach(function(item){
                                        let info = infos.filter(function(s){return s.target_date === item.target_date})[0];
                                        if(!info){
                                            info = {
                                                user_count_valid:0,
                                                stamp_contract:0,
                                                stamp_count:0,
                                                user_count_activity:0,
                                                stamp_over_count:0
                                            }
                                        }
                                        let completed_time = parseInt(item.circular_completed_count) === 0 ? 0 : Math.round(item.circular_completed_total_time/item.circular_completed_count,2);
                                        let completed_per = parseInt(item.circular_applied_count) === 0 ? 0:Math.round(item.circular_completed_count/item.circular_applied_count*100,2);
                                        let usage_per = info.user_count_valid === 0 ? 0 : Math.round(info.user_count_activity/info.user_count_valid*100,2);
                                        outText += "\r\n";
                                        outText +=
                                            company_id
                                            + ',' + company_name
                                            + ',' + item.target_date
                                            + ',' + info.stamp_contract
                                            + ',' + info.stamp_count
                                            + ',' + info.user_count_valid
                                            + ',' + info.user_count_activity
                                            + ',' + item.circular_applied_count
                                            + ',' + item.circular_completed_count
                                            + ',' + completed_time
                                            + ',' + item.multi_comp_out
                                            + ',' + item.multi_comp_in
                                            + ',' + completed_per+'%'
                                            + ',' + usage_per+'%'
                                            + ',' + (parseInt(info.stamp_over_count) < 0 ? 0 : info.stamp_over_count);
                                    })
                                }
                                outText += "\r\n";
                                let blob = new Blob([outText], { type: "text/csv" });
                                let filename = "企業詳細データ.csv";
                                if (window.navigator.msSaveBlob) {
                                    window.navigator.msSaveBlob(blob, filename);
                                }
                                else {
                                    let a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                }
                            }
                        });

                };
                $scope.downloadHostSummaryData = function(){

                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/downloadGuestSummaryInfo/"+$scope.statistics_range + ($scope.company_id?"/"+$scope.company_id:"")+ (($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"/guest"))
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            }else{
                                const infos = event.data.info;

                                const sumInfos = event.data.sum_info;
                                let title = ['企業ID','企業名','対象日','契約数','印面登録数','有効ユーザ数','アクティビティユーザ数','申請数','回覧完了数','平均時間（回覧開始～終了）（h）','社外経由数（送信）','社外経由数（受信）','回覧完了率','利用率（アクティビティユーザ数／有効ユーザ数）','超過登録印面数'];
                                let outText = '\uFEFF';
                                for (let i = 0; i < title.length; i++) {
                                    if(i == 0){
                                        outText += title[i];
                                    } else {
                                        outText +=  ','+ title[i];
                                    }
                                }
                                let company_id = '-';
                                let company_name = '-';
                                if($('#company_id').length > 0 ){

                                    if($('#company_id').val() !== '' && $('#company_id').val() !== '-1'){
                                        company_id = $('#company_id').val();
                                        company_name =  $('#company_id option:selected').text();
                                    }

                                }else{
                                    company_id = $scope.current_company_id;
                                    company_name = $scope.current_company_name;
                                }
                                if(Array.isArray(sumInfos)){
                                    sumInfos.forEach(function(item){
                                        let info = infos.filter(function(s){return s.target_date === item.target_date})[0];
                                        if(!info){
                                            info = {
                                                user_count_valid:0,
                                                stamp_contract:0,
                                                stamp_count:0,
                                                user_count_activity:0,
                                                stamp_over_count:0
                                            }
                                        }
                                        let completed_time = parseInt(item.circular_completed_count) === 0 ? 0 : Math.round(item.circular_completed_total_time/item.circular_completed_count,2);
                                        let completed_per = parseInt(item.circular_applied_count) === 0 ? 0:Math.round(item.circular_completed_count/item.circular_applied_count*100,2);
                                        let usage_per = info.user_count_valid === 0 ? 0 : Math.round(info.user_count_activity/info.user_count_valid*100,2);
                                        outText += "\r\n";
                                        outText +=
                                            company_id
                                            + ',' + company_name
                                            + ',' + item.target_date
                                            + ',' + info.stamp_contract
                                            + ',' + info.stamp_count
                                            + ',' + info.user_count_valid
                                            + ',' + info.user_count_activity
                                            + ',' + item.circular_applied_count
                                            + ',' + item.circular_completed_count
                                            + ',' + completed_time
                                            + ',' + item.multi_comp_out
                                            + ',' + item.multi_comp_in
                                            + ',' + completed_per+'%'
                                            + ',' + usage_per+'%'
                                            + ',' + (parseInt(info.stamp_over_count) < 0 ? 0 : info.stamp_over_count);
                                    })
                                }
                                outText += "\r\n";
                                let blob = new Blob([outText], { type: "text/csv" });
                                let filename = "企業詳細データ.csv";
                                if (window.navigator.msSaveBlob) {
                                    window.navigator.msSaveBlob(blob, filename);
                                }
                                else {
                                    let a = document.createElement('a');
                                    a.href = URL.createObjectURL(blob);
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    document.body.removeChild(a);
                                }
                            }
                        });
                };
                // グラフ表示(非HOST企業用)
                $scope.showChart = function(){
                    var yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    var newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/showChart/" + range + ($scope.company_id?"/"+$scope.company_id:"")+ (($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"/guest"))
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                chart(event.data.info);
                            }
                        });
                };
                // グラフ表示(HOST企業用)
                $scope.showHostCompanyChart = function(){
                    var yearmonth = $scope.yearmonth.split(",");
                    let range = $("input[name='target-month']:checked").val();
                    var newUrl = location.origin+location.pathname+"?year="+yearmonth[0]+"&month="+yearmonth[1]+"&range="+range;
                    if($scope.company_id) newUrl += "&company_id=" + $scope.company_id;
                    window.history.pushState({}, '', decodeURIComponent(newUrl));
                    $rootScope.$emit("showLoading");
                    $http.get(link_ajax + "/showGuestCompanyChart/" + range + ($scope.company_id?"/"+$scope.company_id:"")+ (($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"/guest"))
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if (event.data.status == false) {
                                $(".message").append(showMessages(event.data.message, 'danger', 10000));
                            } else {
                                chart(event.data.info);
                            }
                        });
                };
                function initData(){
                    let dataList = $scope.header_info.data_list;
                    if($scope.header_info){
                        // ユーザー
                        lineChart(dataList.map(function(s) { return s.user_count_valid}),'userChart');
                        // ストレージ
                        pieChart(dataList.map(function(s) { return s.storage_sum_re}),'storageChart');
                        // 登録総印鑑数
                        lineChart(dataList.map(function(s) { return s.stamp_count}),'stampChart');
                        // タイムスタンプ使用回数
                        lineChart(dataList.map(function(s) { return s.timestamp_count}),'timestampChart');

                        //
                        statisticsChart($scope.header_info.statistics_info);

                        fileStatistics($scope.header_info.file_statistics_info);
                        storageStatistics(dataList);
                        userStatistics(dataList);
                        stampStatistics(dataList)
                        timestampStatistics(dataList)

                    }
                }
                // グラフ
                function chart(infos){
                    if(infos.disk_usage_email.length){
                        $('#disk-usage-nodata').hide();
                        $('#disk-usage-chart').show();
                        const sorted = infos.disk_usage_value.sort(function (val1,val2){
                            return val1 - val2;
                        });
                        const maxValue = sorted[infos.disk_usage_value.length-1];
                        const unit = maxValue <= 0.5 * 1024 * 1024 ? 'KB' : 'MB';
                        const diskUsageChart = echarts.init(document.getElementById('disk-usage-chart'));
                        const diskUsageOption = {
                            tooltip: {
                                formatter: function (val) {
                                    return (unit === 'KB' ? (val.data / 1024).toFixed(2):(val.data / (1024 * 1024)).toFixed(2)) + unit;
                                }
                            },
                            grid: {
                                containLabel: true,
                                top: 20,
                                bottom: 40,
                                left:20,
                                right:20
                            },
                            xAxis: {
                                axisLabel: {
                                    formatter: function (val) {
                                        const formattedVal = unit === 'KB' ? val / 1024 : val / (1024 * 1024);
                                        if (formattedVal < 0.95 && formattedVal > 0) {
                                            return formattedVal.toFixed(1) + unit;
                                        } else {
                                            return formattedVal.toFixed(0) + unit;
                                        }                                    }
                                    }
                            },
                            yAxis: [{
                                data: infos.disk_usage_email,
                                axisLabel: {
                                    formatter: function (val) {
                                        if(val.length>40){
                                            let emailChartBefore = val.substring(0,40);
                                            let emailChartLast = val.substring(40,val.length)
                                            let emailChart = emailChartBefore.concat('\n',emailChartLast);
                                            return emailChart;
                                        }else{
                                            return val;
                                        }
                                    },
                                    fontSize: 9,
                                },
                            }],
                            series: [
                                {
                                    type: 'bar',
                                    itemStyle:{
                                        normal: {
                                            color: '#4BB2C5'
                                        }
                                    },
                                    label: {
                                        normal: {
                                            show: true,
                                            formatter: function(val){
                                                return (unit === 'KB' ? (val.data / 1024).toFixed(2):(val.data / (1024 * 1024)).toFixed(2));
                                            },
                                            color: '#0000FF',
                                            position: 'right'
                                        }
                                    },
                                    data: infos.disk_usage_value
                                }
                            ]
                        };
                        diskUsageChart.setOption(diskUsageOption);
                    }else{
                        $('#disk-usage-nodata').show();
                        $('#disk-usage-chart').hide();
                    }
                    if(infos.month_requests_month){
                        $('#month-requests-nodata').hide();
                        $('#month-requests-chart').show();
                        // echarts init
                        var monthRequestsChart = echarts.init(document.getElementById('month-requests-chart'));

                        // setting
                        var monthRequestsOption = {
                            tooltip: {
                                formatter: function (val) {
                                    return val.data + '件';
                                }
                            },
                            xAxis: {
                                data: infos.month_requests_month,
                                axisLabel: {
                                    formatter: function (val) {
                                        return val + '月';
                                    }
                                }
                            },
                            grid: {
                                top:20,
                                bottom:40,
                            },
                            yAxis: {
                                axisLabel: {
                                    formatter: function (val) {
                                        return val + '件';
                                    }
                                },
                            },
                            series: [{
                                type: 'line',
                                itemStyle:{
                                    normal: {
                                        color: '#4BB2C5'
                                    }
                                },
                                label: {
                                    normal: {
                                        show: true,
                                        formatter: function(val){
                                            return val.value === 0 ? '':val.value;
                                        },
                                        color: '#0000FF',
                                        position: 'right'
                                    }
                                },
                                data: infos.month_requests_cnt
                            }]
                        };
                        monthRequestsChart.setOption(monthRequestsOption);
                    }else{
                        $('#month-requests-nodata').show();
                        $('#month-requests-chart').hide();
                    }
                };
                function pieChart(data,elementId){
                    const dom = document.getElementById(elementId);
                    const myChart = echarts.init(dom);
                    const option = {
                        grid: {
                            bottom: 0,
                            left: 0,
                            right: '0%'
                        },
                        title: {
                            left: 'center'
                        },
                        tooltip: {
                            trigger: 'item'
                        },

                        color:['#2F4554', '#a7ea96'],
                        series: [
                            {
                                type: 'pie',
                                radius:  [0,'68%'],
                                hoverOffset:"0",
                                data: [
                                    {value: $scope.header_info.storage_sum_re, name: '使用',itemStyle:{
                                        color:'#FFFFFF'
                                        }},
                                    {value: $scope.header_info.user_count_valid * 1024-$scope.header_info.storage_sum_re, name: '未使用',itemStyle:{
                                            color:'#FFFFFF'
                                        }},
                                ],
                                hoverAnimation:true,
                                emphasis: {
                                    itemStyle: {
                                        shadowBlur: 0,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                },
                                label: {
                                    normal: {
                                        position: 'inner',
                                        show : false
                                    }
                                },
                            },
                            {
                                name: '外边框',
                                type: 'pie',
                                clockWise: false,
                                hoverAnimation:false,
                                radius: ['70%', '71%'],//边框大小
                                center: ['50%', '50%'],//边框位置
                                selectedMode:false,
                                tooltip: {
                                    show: false
                                },
                                label: {
                                    normal: {
                                        show: false
                                    },
                                    emphasis: {
                                        show: false
                                    }
                                },
                                data: [{
                                    value: 10,
                                    itemStyle: {
                                        normal: {
                                            borderWidth: 1,//设置边框粗细
                                            borderColor: '#000'//边框颜色
                                        }
                                    }
                                }]
                            },
                        ]
                    };

                    if (option && typeof option === 'object') {
                        myChart.setOption(option);
                    }
                };
                function lineChart(data,elementId){
                    const dom = document.getElementById(elementId);
                    const myChart = echarts.init(dom);
                    const option = {
                        tooltip: {
                            formatter: function (val) {

                                return val.value;
                            }
                        },
                        xAxis: {
                            type: 'category',
                            data: data,
                            show:false,
                        },
                        yAxis: {
                            type: 'value',
                            show:false,
                        },
                        series: [{
                            data: data,
                            type: 'line',
                            smooth: true,
                            symbolSize:1,
                            itemStyle:{
                                normal: {
                                    color: '#4BB2C5'
                                }
                            }
                        }]
                    };

                    if (option && typeof option === 'object') {
                        myChart.setOption(option);
                    }

                };
                function storageStatistics(dataList){
                    const lastMonthDate = "{{date('Y-m-d',strtotime('-30 day'))}}";
                    const lastMonthData = dataList.filter(function(s){return s.target_date === lastMonthDate})[0];
                    if(lastMonthData){
                        if(parseInt(lastMonthData.storage_sum_re) !== 0){
                            const diff = parseInt($scope.header_info.storage_sum_re) - parseInt(lastMonthData.storage_sum_re);
                            const rate = Math.round(diff/lastMonthData.storage_sum_re*100,2);
                            $('#rate').html( Math.abs(rate)+'%');
                            if(diff >=0 ){
                                $scope.storageUpDownFlag = true;
                            }else{
                                $scope.storageUpDownFlag = false;
                            }
                        }else{
                            $scope.storageUpDownFlag = true;
                        }

                    }else{
                        $scope.storageUpDownFlag = true;
                    }
                };
                function userStatistics(dataList){
                    const lastMonthDate = "{{date('Y-m-d',strtotime('-30 day'))}}";
                    const lastMonthData = dataList.filter(function(s){return s.target_date === lastMonthDate})[0];

                    if(lastMonthData){
                        if(parseInt(lastMonthData.user_count_valid) !== 0){
                            const diff = parseInt($scope.header_info.user_count_valid) - parseInt(lastMonthData.user_count_valid);
                            const rate = Math.round(diff/lastMonthData.user_count_valid*100,2);
                            $('#user_rate').html( Math.abs(rate)+'%');
                            if(diff >=0 ){
                                $scope.userUpDownFlag = true;
                            }else{
                                $scope.userUpDownFlag = false;
                            }
                        }else{
                            $scope.userUpDownFlag = true;
                        }

                    }else{
                        $scope.userUpDownFlag = true;
                    }
                }
                function stampStatistics(dataList){
                    const lastMonthDate = "{{date('Y-m-d',strtotime('-30 day'))}}";
                    const lastMonthData = dataList.filter(function(s){return s.target_date === lastMonthDate})[0];
                    if(lastMonthData){
                        if(parseInt(lastMonthData.stamp_count) !== 0){
                            const diff = parseInt($scope.header_info.stamp_count) - parseInt(lastMonthData.stamp_count);
                            const rate = Math.round(diff/lastMonthData.stamp_count*100,2);
                            $('#stamp_rate').html( Math.abs(rate)+'%');
                            if(diff >=0 ){
                                $scope.stampUpDownFlag = true;
                            }else{
                                $scope.stampUpDownFlag = false;
                            }
                        }else{
                            $scope.stampUpDownFlag = true;
                        }

                    }else{
                        $scope.stampUpDownFlag = true;
                    }
                }
                function timestampStatistics(dataList){
                    const lastMonthDate = "{{date('Y-m-d',strtotime('-30 day'))}}";
                    const lastMonthData = dataList.filter(function(s){return s.target_date === lastMonthDate})[0];
                    if(lastMonthData){
                        if(parseInt(lastMonthData.timestamp_count) !== 0){
                            const diff = parseInt($scope.header_info.timestamp_count) - parseInt(lastMonthData.timestamp_count);
                            const rate = Math.round(diff/lastMonthData.timestamp_count*100,2);
                            $('#timestamp_rate').html( Math.abs(rate)+'%');
                            if(diff >=0 ){
                                $scope.timestampUpDownFlag = true;
                            }else{
                                $scope.timestampUpDownFlag = false;
                            }
                        }else{
                            $scope.timestampUpDownFlag = true;
                        }

                    }else{
                        $scope.storageUpDownFlag = true;
                    }
                }
                function fileStatistics(fileInfo){
                    $("#pdfArea").css("width",'');
                    $("#excelArea").css("width",'');
                    $("#wordArea").css("width",'');
                    $('#downloadPdfArea').css('width','');
                    if(fileInfo){
                        const total = parseInt(fileInfo.upload_count_excel)+
                            parseInt(fileInfo.upload_count_pdf)+parseInt(fileInfo.upload_count_word);
                        // アップロード数　＞　ダウンロード数
                        if(total > parseInt(fileInfo.download_count_pdf)){
                            $("#uploadArea").css("width",'100%');
                            $('#downloadPdfArea').css('width',fileInfo.download_count_pdf/total*100+'%')

                        }else{
                            $("#downloadPdfArea").css("width",'100%');
                            $('#uploadArea').css('width',total/fileInfo.download_count_pdf*100+'%')
                        }
                        if(total !== 0){
                            // PDF
                            const pdfWidth = Math.round(fileInfo.upload_count_pdf/total*100,2);

                            $("#pdfArea").css("width",pdfWidth+'%');
                            $("#pdfArea").attr('title',fileInfo.upload_count_pdf);

                            // EXCEL
                            const excelWidth = Math.round(fileInfo.upload_count_excel/total*100,2);
                            $("#excelArea").css("width",excelWidth+'%');
                            if(excelWidth !== 0){
                                $("#excelArea").css("margin-left",'5px');
                            }else{
                                $("#excelArea").css("margin-left",'0');

                            }
                            $("#excelArea").attr('title',fileInfo.upload_count_excel);

                            // EXCEL
                            const wordWidth = Math.round(fileInfo.upload_count_word/total*100,2);
                            $("#wordArea").css("width",wordWidth+'%');
                            if(wordWidth !== 0){
                                $("#wordArea").css("margin-left",'5px');
                            }else{
                                $("#wordArea").css("margin-left",'0');

                            }
                            $("#wordArea").attr('title',fileInfo.upload_count_word);


                        }
                        if(fileInfo.download_count_pdf && fileInfo.download_count_pdf !== 0){
                            $("#downloadPdfArea").attr('title',fileInfo.download_count_pdf);
                        }else{
                            $('#downloadPdfArea').css('width','');
                        }


                    }
                };
                function statisticsChart(data){
                    const statistics_type = parseInt($scope.statistics_type);
                    const xData = data.map(function (s){ return s.target_date});
                    let yData = [];
                    let option={};
                    let legend=[];
                    let chartData=[]
                    let defaultColor=['#5B9BD5','#ED7D31','#A2A2A2','#FFC000'];
                    let color=[];
                    switch (statistics_type){
                        case 0:
                            legend=$scope.shuukei_koumoku[statistics_type].child.map(function (s,i){
                                if (s.checked){
                                    color.push(defaultColor[i])
                                    return s.name
                                }
                                return null
                            }).filter(function(s){
                                return s!=null;
                            });
                            chartData=legend.map(function(s){
                                if (s=='契約数'){
                                    return data.map(function (s){ return s.stamp_contract})
                                }else if(s=='印面登録数'){
                                    return data.map(function (s){ return s.stamp_count})
                                }else if(s=='有効ユーザ数'){
                                    return data.map(function (s){ return s.user_count_valid});
                                }else if(s=='アクティビティユーザ数'){
                                    return data.map(function (s){ return s.user_count_activity});
                                }
                                return null
                            }).filter(function(s){
                                return s!=null
                            })
                             if (chartData.length>0){
                                 option=buildBarChart(legend,xData,chartData,false,color);
                             }
                            break;
                        case 1:
                            legend=$scope.shuukei_koumoku[statistics_type].child.map(function (s,i){
                                if (s.checked){
                                    color.push(defaultColor[i])
                                    return s.name
                                }
                                return null
                            }).filter(function(s){
                                return s!=null;
                            });
                            chartData=legend.map(function(s){
                                if (s=='回覧完了率'){
                                    return data.map(function(s){
                                        if(parseInt(s.circular_applied_count) === 0){
                                            return 0;
                                        }
                                        return Math.round(parseInt(s.circular_completed_count)/parseInt(s.circular_applied_count)*100,2);
                                    });
                                }else if(s=='利用率（アクティビティユーザ数／有効ユーザ数）'){
                                    return data.map(function (s){
                                        if(s.user_count_valid === 0){
                                            return 0;
                                        }
                                        return Math.round(s.user_count_activity/s.user_count_valid*100,2);
                                    });
                                }
                                return null
                            }).filter(function(s){
                                return s!=null
                            })
                            if (chartData.length>0){
                                option=buildBarChart(legend,xData,chartData,true,color);
                            }
                            break;
                        case 2:
                            legend=$scope.shuukei_koumoku[statistics_type].child.map(function (s,i){
                                if (s.checked){
                                    color.push(defaultColor[i])
                                    return s.name
                                }
                                return null
                            }).filter(function(s){
                                return s!=null;
                            });
                            chartData=legend.map(function(s){
                                if (s=='申請数'){
                                    return data.map(function (s){ return s.circular_applied_count});
                                }else if(s=='回覧完了数'){
                                    return data.map(function (s){ return s.circular_completed_count});
                                }
                                return null
                            }).filter(function(s){
                                return s!=null
                            })
                            if (chartData.length>0){
                                option=buildBarChart(legend,xData,chartData,false,color);
                            }
                            break;
                        case 3:
                            chartData=data.map(function (s){
                                if(parseInt(s.circular_completed_count) === 0){
                                    return 0;
                                }
                                return Math.round(parseInt(s.circular_completed_total_time)/parseInt(s.circular_completed_count),2);
                            });
                            option=buildLineChart(xData,chartData);
                            break;
                        case 4:
                            chartData=data.map(function (s){ return s.multi_comp_out});
                            option=buildLineChart(xData,chartData);
                            break;
                        case 5:
                            chartData = data.map(function (s){ return s.multi_comp_in});
                            option=buildLineChart(xData,chartData);
                            break;
                        case 6:
                            chartData=data.map(function(s){
                                        return s.stamp_over_count < 0 ? 0 : s.stamp_over_count;
                                    });
                            option=buildLineChart(xData,chartData);
                            break;



                    }

                    const dom = document.getElementById('statisticsChart');
                    const myChart = echarts.init(dom);

                    myChart.clear();
                    myChart.setOption(option)
                    $scope.chart=myChart;
                };
                function buildBarChart(legend,xData,yData,is_percent,color){
                    return {
                        grid: {
                            left:'50px',
                            right:'30px',
                            top:'20px',
                            bottom:'25%'
                        },
                        legend:{
                            data: legend,
                            bottom:0,
                            selectedMode:false,
                            textStyle:{
                                fontSize:18,
                                color:'#666',
                                padding:[0,30,0,0]
                            }
                        },
                        tooltip: {
                            formatter: function (val) {
                                if(is_percent){
                                    return val.value + '%';
                                }
                                return val.value;
                            }
                        },
                        color:color,
                        xAxis: {
                            type: 'category',
                            data: xData.map(function(_){
                                let date=_.split('-')
                                let month=date[1]>9?date[1]:date[1][1]
                                return month+'月'+date[2]+'日';
                            }),
                            axisLabel:{
                                interval: 0,
                                rotate:'30',//旋转度数
                                fontSize:18,
                                margin:15,
                                color:'#666'
                            },
                            // axisLine:{
                            //     lineStyle:{
                            //         color:'rgba(0,0,0,.125)',
                            //     }
                            //
                            // },
                            // axisTick:{
                            //     lineStyle:{
                            //         color:'rgba(0,0,0,1)',
                            //     }
                            // },
                        },
                        yAxis: {
                            axisLine:{
                                show:false
                            },
                            axisTick:{
                                show:false
                            },
                            type: 'value',
                            axisLabel:{
                                formatter:function(val){
                                    return val;
                                },
                                fontSize:16,
                                color:'#666',
                            },
                            axisLabel: {
                                formatter:function (val) {
                                    if(is_percent){
                                        return val + '%';
                                    }
                                    return val;
                                }
                            }
                        },

                        series:legend.map(function (s,i){
                            return {
                                name:s,
                                type:'bar',
                                data:yData[i]
                            }
                        }),
                    };
                }
                function buildLineChart(xData,yData,legend){
                    return {
                        grid: {
                            left:'40px',
                            right:'30px',
                            top:'20px',
                            bottom:'10%'
                        },
                        tooltip: {
                            formatter: function (val) {
                                return val.value;
                            }
                        },
                        xAxis: {
                            type: 'category',
                            data: xData,
                            axisLabel:{
                                interval: 0,
                                rotate:'-30',//旋转度数
                            }
                        },
                        yAxis: {
                            type: 'value',
                            axisLabel: {
                                formatter:function (val) {
                                    return val;
                                }
                            }
                        },
                        series: [{
                            data: yData,
                            type: 'line',
                            itemStyle:{
                                normal: {
                                    color: '#4BB2C5'
                                }
                            },
                        }]
                    }
                }
                $scope.downloadHisAdmin = function() {

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'管理者操作履歴データを出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            callSuccess: function(){

                                var from_date,to_date;

                                if($("#from_date").val()){
                                    from_date = $("#from_date").val().toString();
                                }else{
                                    from_date = '9999-99-99';
                                }

                                if($("#to_date").val()){
                                    to_date = $("#to_date").val().toString();
                                }else{
                                    to_date = '9999-99-99';
                                }

                                var url = link_ajax + "/csv/admin?company_id=" + $scope.company_id + '&from_date=' + from_date + '&to_date=' + to_date;

                                location.href = url;

                            }

                        });
                };

                $scope.downloadHisUser = function() {

                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'利用者操作履歴データを出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            callSuccess: function(){

                                var from_date,to_date;

                                if($("#from_date").val()){
                                    from_date = $("#from_date").val().toString();
                                }else{
                                    from_date = '9999-99-99';
                                }

                                if($("#to_date").val()){
                                    to_date = $("#to_date").val().toString();
                                }else{
                                    to_date = '9999-99-99';
                                }

                                var url = link_ajax + "/csv/user?company_id=" + $scope.company_id + '&from_date=' + from_date + '&to_date=' + to_date;

                                location.href = url;

                            }

                        });

                };
                $scope.downloadCsvStampLedger = function() {
                    $rootScope.$emit("showMocalConfirm",
                    {
                        title:'捺印台帳データを出力します。<br />実行しますか？',
                        btnSuccess:'はい',
                        callSuccess: function(){
                            if(!$("#select_month").val()){
                                $(".message_stampcsv").append(showMessages(['対象月を選択してください'], 'danger', 10000));
                                return;
                            }

                            $rootScope.$emit("showLoading");
                            var select_month = $("#select_month").val().toString();
                            var serial = $("#s_serial").val().toString();
                            $http.post(link_ajax_stamp_ledger, { company_id: $scope.company_id, select_month: select_month, serial: serial })
                                .then(function(event) {
                                    $rootScope.$emit("hideLoading");
                                    $ret = event.data.status == false ? 'danger' : 'success';
                                    $(".message_stampcsv").append(showMessages(event.data.message, $ret, 10000));
                                });

                        }
                    });
                };

                $scope.downloadCsvRegistration = function(){
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'登録情報を出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_csv2, { })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".registration_csv").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $(".registration_csv").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                };

                //PAC_5-897 Start
                $scope.downDiskUsages = function(){
                    let range = $("input[name='target-month']:checked").val();
                    if(!range){
                       range = 6;
                    }
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'利用者ファイル容量を出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_disk_usage, { company_id: $scope.company_id, range: range })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message_diskUsagesCsv").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $(".message_diskUsagesCsv").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                };
                $scope.downHostDiskUsages = function(){
                    let range = $("input[name='target-month']:checked").val();
                    if(!range){
                        range = 6;
                    }
                    $rootScope.$emit("showMocalConfirm",
                        {
                            title:'利用者ファイル容量を出力します。<br />実行しますか？',
                            btnSuccess:'はい',
                            size:"default",
                            callSuccess: function(){
                                $rootScope.$emit("showLoading");
                                $http.post(link_ajax_disk_host_usage, { company_id: ($scope.company_id ? $scope.company_id:""), range: range, isGuest:(($scope.host_company == $scope.company_id) || ($scope.company_id == -1) || !$scope.company_id?"":"guest") })
                                    .then(function(event) {
                                        $rootScope.$emit("hideLoading");
                                        if(event.data.status == false){
                                            $(".message_diskUsagesCsv").append(showMessages(event.data.message, 'danger', 10000));
                                        }else{
                                            $(".message_diskUsagesCsv").append(showMessages(event.data.message, 'success', 10000));
                                        }
                                    });
                            }
                        });
                };
                //PAC_5-897 End
                
                $scope.stampPreview = function() {
                    $rootScope.$emit("showLoading",{'ttl': 60000});
                    var serial = $("#serial").val().toString().trim();
                    if(!serial){
                        $rootScope.$emit("hideLoading");
                        $(".preview_stamp").append(showMessages(['正しい印鑑シリアルを入力してください'], 'danger', 10000));
                        return
                    }

                    $http.get(link_ajax + "/previewStamp/" +serial)
                        .then(function(event) {
                            $rootScope.$emit("hideLoading");
                            if(event.data.status == false){
                                $(".preview_stamp").append(showMessages(event.data.message, 'danger', 10000));
        }else{
                                $scope.previewStamp = event.data.info;
                            }
                        });
                };
                /*PAC_5-2289 S*/
                $scope.timeFocus = function () {
                    if(GetIEVersion() > 0 ){
                        if ($('.monthpicker')) {
                            $(".monthpicker").remove();
                        }
                        var y;
                        if ($('#select_month').val()) {
                            var _time = $('#select_month').val().split('-');
                            y = _time[0];
                        }
                        $('#select_month').monthpicker({
                            selectYears: y ? y : '',
                            onMonthSelect: function (m, y) {
                                m = (parseInt(m) + 1);
                                if (m.toString().length == 1) {
                                    m = '0' + m;
                                }
                                $('#select_month').val(y + '-' + m);
                            }
                        });
                    }
                }
                /*PAC_5-2289 E*/

            });
        }else{
            throw new Error("Something error init Angular.");
        }
        $(document).ready(function() {
            $('.select2').select2({
                allowClear: true,
                "language": {
                    "noResults": function(){
                        return "データがありません";
                    }
                }
            });
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
        .block{
            border: 1px solid rgba(0,0,0,.125);
            border-radius:.25rem;
            padding: 10px;
            height: 140px;
        }
        .block .title{
            /*padding: 10px;*/
        }
        .block .number{
            font-size: 30px;
        }
        .dot {
            width: 10px;
            height: 10px;
            border-radius: 7px;
            display: inline-block;
            vertical-align: middle;
            position: relative;

        }
        .orange{
            background: orange;
        }
        .dark-blue{
            background:darkblue;
        }
        .green{
            background:green;
        }
        .pink{
            background:pink;
        }
    </style>
@endpush
