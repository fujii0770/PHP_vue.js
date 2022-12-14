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
        /* ?????????????????????????????? */
        .notices{
            page-break-after: always;
            page-break-inside: avoid;
        }
        .notices:last-child{
            page-break-after: auto;
        }
        /* ??????????????????????????????????????????????????????????????????????????? */ 
        .chrLimit {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis !important;
            /*IE??????*/
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
            <span> ??? </span>
            <span class="font-re">{{$data['month']}}</span>
            <span> ??????</span>
        </div>
        <div class="title-name">???????????????</div>
    </div>

    <div class="table-info">
        <table>
            <tr height="43px">
                <td width="12%"><span>???????????????</span></td>
                <td><span>{{$data['company_name']}}</span></td>
                <td width="11%"><span>???????????????</span></td>
                <td width="11%"><span>???????????????</span></td>
                <td width="11%"><span>?????????</span></td>
            </tr>
            <tr height="45px">
                <td><span>??????</span></td>
                <td><span>{{$data['user_name']}}</span></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
            </tr>
            <tr height="45px">
                <td><span>???????????????</span></td>
                <td><span class="chrLimit">{{$data['assigned_company']}}</span></td>
            </tr>
        </table>
    </div>

    <div class="table-detail">
        <table>
            <thead>
                <tr>
                    <th class="width-35">??????</th>
                    <th class="width-35">??????</th>
                    <th class="pad">??????</th>
                    <th class="pad">??????</th>
                    <th class="pad">????????????</th>
                    <th class="pad width-60">????????????</th>
                    <th width="60px">?????????</th>
                    <th class="pad width-190">????????????</th>
                    <th class="pad width-95">??????</th>
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
                    <td class="center" colspan="2">????????????</td>
                    <td class="center">{{$data['attendanceDays']}}???</td>
                    <td class="center" colspan="2">?????????</td>
                    <td class="center">{{$data['totalWorkingTime']}}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="notices">
        <table>
            <tr>
                <td><span>????????????</span></td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
