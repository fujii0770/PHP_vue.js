
@extends('../layouts_plain.email')

@section('content')
下記の情報取得リクエストが正常終了しました。
ファイルは、{{$file_path}}を参照してください。

[処理番号] {{$id}}
[コマンド] {{$command}}
[受付時間] {{$request_datetime}}
[実行開始時間] {{$execution_start_datetime}}
[実行終了時間] {{$execution_end_datetime}}

@endsection