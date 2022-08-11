<html>
<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'SJIS', sans-serif;
            margin: 50px 40px;
        }
        table {
            font-size: 12pt;
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
        }

        .title {
            font-size: 18pt;
            font-weight: bolder;
        }
        .title-time {
            width: 50%;
            float: left;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }
        .title-name {
            width: 50%;
            float: left;
            border-bottom: 2px solid #000;
            text-align: center;
            margin-bottom: 20px;
        }
        .table-info {
            font-size: 12pt;
            margin-bottom: 15px;
        }
        .table-info td{
            text-align: center;
            border: 1px solid #000;
        }
        .table-info th {
            text-align: center;
            border: 1px solid #000;
        }
        .table-detail th {
            text-align: center;
            border: 1px solid #000;
            font-weight: normal;
        }
        .table-detail th.pad {
            padding-right: 5px;
            padding-left: 5px;
        }
        .table-detail th.width-95, td.width-95 {
            max-width: 95px;
            min-width: 95px;
        }
        .table-detail th.width-70, td.width-70 {
            max-width: 70px;
            min-width: 70px;
        }
        .table-detail th.width-35, td.width-35 {
            max-width: 35px;
            min-width: 35px;
        }
        .table-detail th.width-60, td.width-60 {
            max-width: 60px;
            min-width: 60px;
        }
        .table-detail th.width-190, td.width-190 {
            max-width: 190px;
            min-width: 190px;
        }
        .table-detail td.center {
            text-align: center;
        }
        .table-detail td{
            font-size: 11pt;
            padding-top: 3px;
            height: 28px;
            border: 1px solid #000;
            
        }
        .table-detail th{
            font-size: 12pt;
            padding-bottom: 3px;
            padding-top: 5px;
        }
        .table-detail td.left {
            text-align: left;
            padding-left: 5px;
            padding-right: 15px;
        }
        .font-re {
            font-family: "Noto Sans JP";
        }
        .notices {
            margin-top: 20px;
            width: 100%;
        }
        .notices table {
            border: 1px solid #000 !important;
            height: 80px;
        }
        .notices td {
            text-align: left;
            vertical-align: top;
        }
        .notices span {
            margin-top: 10px;
            margin-left: 10px;
        }
        tr, td{
            page-break-inside: avoid;
        }
        .notices table, .notices tr, .notices td{
            page-break-inside: avoid;
        }
        /* 勤務表単位の改ページ */
        .notices{
            page-break-after: always;
            page-break-inside: avoid;
        }
        .notices:last-child{
            page-break-after: auto;
        }
        /* １行で規定文字数を超えると以降はドットラインで省略 */ 
        .chrLimit {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis !important;
            /*IE対策*/
            line-height: 1.5em; 
            max-height: 1.5em; /* row * line-height */
        }
    </style>
</head>
<body>

@foreach($datas as $index => $data)  
    <div class="title">
        <div class="title">
        <div class="title-time">
            <span class="font-re">{{$data['year']}}</span>
            <span> 年 </span>
            <span class="font-re">{{$data['month']}}</span>
            <span> 月度</span>
        </div>
        <div class="title-name">出勤確認表</div>
    </div>

    <div class="table-info">
        <table>
            <tr height="43px">
                <td width="12%"><span>所属会社名</span></td>
                <td><span>{{$data['company_name']}}</span></td>
                <td width="11%"><span>所属承認印</span></td>
                <td width="11%"><span>現場承認印</span></td>
                <td width="11%"><span>申請印</span></td>
            </tr>
            <tr height="45px">
                <td><span>氏名</span></td>
                <td><span>{{$data['user_name']}}</span></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
            </tr>
            <tr height="45px">
                <td><span>配属現場名</span></td>
                <td><span class="chrLimit">{{$data['assigned_company']}}</span></td>
            </tr>
        </table>
    </div>

    <div class="table-detail">
        <table>
            <thead>
                <tr>
                    <th class="width-35">日付</th>
                    <th class="width-35">曜日</th>
                    <th class="pad">出勤</th>
                    <th class="pad">退勤</th>
                    <th class="pad">休憩時間</th>
                    <th class="pad width-60">稼働時間</th>
                    <th width="60px">休暇等</th>
                    <th class="pad width-190">作業内容</th>
                    <th class="pad width-95">備考</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['timeCardDetails'] as $index => $timeCard)
                    <tr>
                        @if($index<10)
                            <td class="center"><span>0{{$index}}</span></td>
                        @else
                            <td class="center"><span>{{$index}}</span></td>
                        @endif
                        <td class="center"><span>{{$timeCard['date']}}</span></td>
                        <td class="center"><span>{{$timeCard['work_start_time']}}</span></td>
                        <td class="center"><span>{{$timeCard['work_end_time']}}</span></td>
                        <td class="center"><span>{{$timeCard['break_time']}}</span></td>
                        <td class="center width-60"><span>{{$timeCard['actual']}}</span></td>
                        <td class="center" width="60px"><span>{{$timeCard['vacation']}}</span></td>
                        <td class="left width-190 chrLimit">{{$timeCard['work_detail']}}</td>
                        <td class="left width-95 chrLimit">{{$timeCard['memo']}}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="center" colspan="2">出勤日計</td>
                    <td class="center">{{$data['attendanceDays']}}日</td>
                    <td class="center" colspan="2">時間計</td>
                    <td class="center">{{$data['totalWorkingTime']}}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="notices">
        <table>
            <tr>
                <td><span>特記事項</span></td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
