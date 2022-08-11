@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>下記の情報取得リクエストが正常終了しました。<br>
            ファイルは、{{$file_path}}を参照してください。<br><br>
            [処理番号] {{$id}}<br>
            [コマンド] {{$command}}<br>
            [受付時間] {{$request_datetime}}<br>
            [実行開始時間] {{$execution_start_datetime}}<br>
            [実行終了時間] {{$execution_end_datetime}}<br>
        </p>

    </div>
@endsection
 