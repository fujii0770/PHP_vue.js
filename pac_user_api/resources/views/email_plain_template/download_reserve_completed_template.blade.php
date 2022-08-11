@extends('../layouts_plain.email')

@section('content')
ダウンロードファイルの準備が完了しました。
ダウンロード期限内にファイルのダウンロードをお願い致します。
ファイル名：{{ $file_name }}
ダウンロード期限：{{ $dl_period }}

ログイン画面に移動 : {{ config('app.new_app_url') }}


@endsection

