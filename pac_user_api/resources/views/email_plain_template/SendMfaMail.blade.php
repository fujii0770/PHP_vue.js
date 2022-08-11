@extends('../layouts_plain.email')

@section('content')
@if($company_name)
対象企業：{{ $company_name }}
対象ID：{{ $user_id }}
@endif
いつもShachihata Cloudをご利用いただきありがとうございます。
{{ $otp }}
この認証コードをログイン画面に入力してください。
この認証コードの有効期限は {{ $otpExpires }} です。
お客様がこのリクエストを行っていない場合、貴社の管理者までお問い合わせください。

@endsection
