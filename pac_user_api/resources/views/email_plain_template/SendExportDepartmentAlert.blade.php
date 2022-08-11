@extends('../layouts_plain.email')

@section('content')
{{ $adminName }} 様 
Shachihata Cloudの部署情報ダウンロードファイルの作成が完了しましたので、ご連絡致します。

ログイン画面に移動: {{ str_replace('app-api','admin',config('app.url')) }}

@endsection
 
