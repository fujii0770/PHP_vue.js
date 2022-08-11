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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .title {
            border-bottom: 2px solid #000;
            font-size: 18pt;
        }
        img {
            margin-top: 10px;
            border: 1px solid #000;
            width: 200px;
        }
        .pl-10 {
            padding-left: 10px;
        }
        .pl-20 {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <h1 class="title">文書情報</h1>
    <p>文書ID</p>
    <p class="pl-10"><strong>{{$document ? $document->id: ''}}</strong></p>
    <br>
    <p>文書名</p>
    <p class="pl-10"><strong>{{$document ? $document->file_name: ''}}</strong></p>
    <br>
    <p>作成日時</p>
    <p class="pl-10"><strong>{{$document ? (new \DateTime($document->create_at))->format('Y/m/d H:i:s'): ''}}</strong></p>
    <br>
    <p>回覧状況</p>
    <p class="pl-10">回覧中</p>
    <br>
    <p>QRコード（文書の回覧状況によってはURLが無効となります。）</p>
    <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={{urlencode($circular_link)}}&choe=UTF-8" title="{{$circular_link}}" />

</body>
</html>