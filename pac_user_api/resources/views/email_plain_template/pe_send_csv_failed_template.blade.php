
@extends('../layouts_plain.email')

@section('content')
下記の情報取得リクエストが失敗しました。

[処理番号] {{$id}}
[コマンド] {{$command}}
[受付時間] {{$request_datetime}}
[実行開始時間] {{$execution_start_datetime}}
[実行終了時間] {{$execution_end_datetime}}
[メッセージ] {{$message1}}

@endsection