@extends('../layouts_plain.email')

@section('content')
@if($company_name)
対象企業：{{ $company_name }}
対象ID：{{ $user_id }}
@endif
いつもShachihata Cloudをご利用いただきありがとうござい
ます。

ご利用のShachihata Cloudアカウントに対して、
初期パスワードを発行いたしました。

パスワード：{{ $password }}

@if(isset($login_url) && $login_url)
{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($login_url)}} : {{ $login_url}}
@else
ログイン画面に移動 : {{ config('app.new_app_url') }}
@endif
お客様がこのリクエストを行っていない場合、
このままこのメールを削除してください。
他人が不正にアカウントにアクセスしていると思われる場合は、
Shachihata Cloudの設定ページで、
ただちにパスワードを変更してください。
@endsection
