@extends('../layouts_plain.email')

@section('content')
Shachihata Cloudをご利用いただきありがとうございます。
お申し込みいただいておりました共通印の準備が整いました。
Shachihata Cloudの管理者サイトにログインし、共通印設定画面でご確認ください。

ログイン画面に移動 : {{ str_replace('app-api','admin',config('app.url')) }}

@endsection
 
