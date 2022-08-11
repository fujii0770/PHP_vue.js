@extends('../layouts_plain.email')

@section('content')
Shachihata Cloudをご利用いただきありがとうございます。

管理者より、
お客様はShachihata Cloudの利用者として登録されました。

Shachihata Cloudのご利用にはお客様のメールアドレスとパスワードが必要です。

パスワード：{{ $password }}

@if(isset($login_url) && $login_url)
{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($login_url)}} : {{ $login_url}}
@else
ログイン画面に移動 : {{ config('app.new_app_url') }}
@endif
他者が不正にアカウントにアクセスしていると思われる場合は、
Shachihata Cloudの設定ページで、
ただちにパスワードを変更してください。
@endsection