<html>
<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'SJIS', sans-serif;
        }

        p {
            margin: 0;
        }

        h1 {
            font-size: 16pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .title {
            border-bottom: 2px solid #000;
            font-size: 18pt;
        }

        .table-wraper {
            padding: 0 20px;
        }

        .table2 th {
            text-align: left;
        }

        .table2 .table-thead th {
            border-bottom: 1px solid #000;
        }

        .table2 tbody tr td {
            border-bottom: 1px solid #CDCDCD;
            padding: 5px 0;
        }

        .span1 {
            color: #666666;
            font-size: 90%;
        }

        .span2 {
            display: inline-block;
            margin: 0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<h1 class="title" style="margin-bottom: 20px">文書情報</h1>
<div class="table-wraper">
    <table class="table1">
        <tr>
            <td><span class="span1">ファイル名</span></td>
        </tr>
        <tr>
            <td style="padding-bottom: 20px"><span>&nbsp;{{$circular_document->file_name}}</span></td>
        </tr>
        <tr>
            <td><span class="span1">作成日</span></td>
            <td><span class="span1">回覧先</span></td>
        </tr>
        <tr>
            <td style="padding-bottom: 20px">
                <span>&nbsp;{{$circular_document ? (new \DateTime($circular_document->create_at))->format('Y/m/d H:i:s'): ''}}</span>
            </td>
            <td style="padding-bottom: 100px" rowspan="6">
                @foreach($circular_users as $index => $circular_user)
                    @if($circular_user->name == '')
                        <span class="span2">&nbsp;・<a
                                    href="mailto:{{$circular_user->email}}">{{$circular_user->email}}</a></span>
                        <br/>
                    @else
                        <span class="span2">&nbsp;・{{$circular_user->name}}</span>
                        <br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a
                                    href="mailto:{{$circular_user->email}}">{{$circular_user->email}}</a></span><br/>
                    @endif
                @endforeach
            </td>
        </tr>
        <tr>
            <td><span class="span1">最終更新</span></td>
        </tr>
        <tr>
            <td style="padding-bottom: 20px">
                <span>&nbsp;{{$circular_document ? (new \DateTime($circular_document->update_at))->format('Y/m/d H:i:s'): ''}}</span>
            </td>
        </tr>
        <tr>
            <td><span class="span1">回覧状況</span></td>
        </tr>
        <tr>
            <td style="padding-bottom: 20px"><span>&nbsp;{{$circular_status}}</span></td>
        </tr>
    </table>
</div>
<h1 class="title" style="margin-top: 40px;margin-bottom: 20px">承認履歴情報</h1>
<div class="table-wraper">
    <table class="table2">
        <tbody>
        <tr class="table-thead">
            <th>日付</th>
            <th>ユーザー</th>
            <th>操作</th>
            <th>詳細</th>
        </tr>
        @foreach($histories as $index => $history)
            @if($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS && empty($history->text))
                @continue
            @endif
            <tr>
                <td width="15.0%">{{$history ? (new \DateTime($history->create_at))->format('Y/m/d H:i:s'): ''}}</td>
                <td width="28.0%"><p>{{$history->operation_name}}</p><a
                            href="mailto:{{$history->operation_email}}">{{$history->operation_email}}</a>
                </td>
                <td width="10.0%">
                    <span>{{$history->is_skip ? 'スキップ' : \App\Http\Utils\CircularOperationHistoryUtils::getCircularUserStatus($history->circular_status)}}</span>
                </td>
                <td width="44.0%">
                    @if($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_CREATE_STATUS)
                    @elseif($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS)
                        <div>
                            <img style="width:40px;" src="data:image/png;base64,{{$history->stamp_image}}" alt="stamp-image">
                        </div>
                    @elseif($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS)
                        <span class="span1">宛先：</span><br/>
                        @foreach($history->acceptors as $key => $acceptor)
                            <span>{{$acceptor->acceptor_name}}</span><br/>
                            <span><a href="mailto:{{$acceptor->acceptor_email}}">{{$acceptor->acceptor_email}}</a></span>
                            @if($key != count($history->acceptors) - 1)
                                <br/>
                            @endif
                        @endforeach
                    @elseif($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS)
                        @if(!empty($history->acceptors))
                            <span class="span1">宛先：</span><br/>
                            @foreach($history->acceptors as $key => $acceptor)
                                <span>{{$acceptor->acceptor_name}}</span><br/>
                                <span><a href="mailto:{{$acceptor->acceptor_email}}">{{$acceptor->acceptor_email}}</a></span>
                                @if($key != count($history->acceptors) - 1)
                                    <br/>
                                @endif
                            @endforeach
                        @endif
                    @elseif($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS)
                        <span class="span1">宛先：</span><br/>
                        @foreach($history->acceptors as $key => $acceptor)
                            <span>{{$acceptor->acceptor_name}}</span><br/>
                            <span><a href="mailto:{{$acceptor->acceptor_email}}">{{$acceptor->acceptor_email}}</a></span>
                            @if($key != count($history->acceptors) - 1)
                                <br/>
                            @endif
                        @endforeach
                    @elseif($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS ||
                            $history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS ||
                            $history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS)
                        <span class="span1">宛先：</span><br/>
                        @foreach($history->acceptors as $key => $acceptor)
                            <span>{{$acceptor->acceptor_name}}</span><br/>
                            <span><a href="mailto:{{$acceptor->acceptor_email}}">{{$acceptor->acceptor_email}}</a></span>
                            @if($key != count($history->acceptors) - 1)
                                <br/>
                            @endif
                        @endforeach
                    @elseif($history->circular_status == \App\Http\Utils\CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS)
                        <span> {!! preg_replace("/\\n/", "<br/>", $history->text) !!}</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<h1 class="title" style="margin-top: 100px;margin-bottom: 20px">テキスト追加履歴</h1>
<div class="table-wraper">
    <table class="table2">
        <thead>
        <tr>
            <th>日付</th>
            <th>ユーザー</th>
            <th>テキスト</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($histories_text_info as $index => $history)
            <tr>
                <td width="15.0%">{{$history ? (new \DateTime($history->create_at))->format('Y/m/d H:i:s'): ''}}</td>
                <td width="28.0%"><p>{{$history->operation_name}}</p><a
                            href="mailto:{{$history->operation_email}}">{{$history->operation_email}}</a>
                </td>
                <td width="46.0%">
                    <span>{!! str_replace(array("\r\n", "\r", "\n"), "<br />", $history->text); !!}</span>
                </td>
                <td width="8.0%">
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<h1 class="title" style="margin-top: 100px;margin-bottom: 20px">添付ファイル情報</h1>
<div class="table-wraper">
    <table class="table2">
        <thead>
        <tr>
            <th>日付</th>
            <th>ユーザー</th>
            <th>ファイル名</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($circular_attachments as $index => $history)
            <tr>
                <td width="15.0%">{{$history ? (new \DateTime($history->create_at))->format('Y/m/d H:i:s'): ''}}</td>
                <td width="28.0%"><p>{{$history->name}}</p><a
                            href="mailto:{{$history->create_user}}">{{$history->create_user}}</a>
                </td>
                <td width="46.0%">
                    <span>{{$history->file_name}}</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
