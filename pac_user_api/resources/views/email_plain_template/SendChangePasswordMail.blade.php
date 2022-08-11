@extends('../layouts_plain.email')

@section('content')
いつもShachihata Cloudをご利用いただきありがといございます。
ご利用の管理者アカウントに新しいパスワードを設定しました。

ログイン画面に移動 : {{ str_replace('app-api','admin',config('app.url')) }}

@endsection
 
