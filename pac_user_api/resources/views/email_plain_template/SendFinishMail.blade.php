@extends('../layouts_plain.email')

@section('content')
@if($company_name)
対象企業：{{ $company_name }}
対象ID：{{ $user_id }}
@endif
いつもShachihata Cloudをご利用いただきありがといございます。
ご利用のアカウントに新しいパスワードを設定しました。

@if(isset($url_domain_id) && $url_domain_id)
SAML機能でログイン : {{ config('app.new_app_url').'/'.config('app.saml_url_prefix').'/'.$url_domain_id }}
@else
ログイン画面に移動 : {{ config('app.new_app_url') }}
@endif
@endsection
 
