@extends('../layouts_plain.email')

@section('content')
いつもShachihata Cloudをご利用いただきありがとうございます。
ご利用の管理者アカウントに対して、
初期パスワードを発行いたしました。

パスワード：{{ $password }}

ログイン画面に移動 : {{ str_replace('app-api','admin',config('app.url')) }}

お客様がこのリクエストを行っていない場合、
このままこのメールを削除してください。
他人が不正にアカウントにアクセスしていると思われる場合は、
Shachihata Cloudの設定ページで、
ただちにパスワードを変更してください。

@endsection
