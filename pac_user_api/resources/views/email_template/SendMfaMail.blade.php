@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        @if($company_name)
        <p>対象企業：{{ $company_name }}<br>
            対象ID：{{ $user_id }}</p>
        @endif
        <p>いつもShachihata Cloudをご利用いただきありがとうございます。</p>
        <p>{{ $otp }}</p>
        <p>この認証コードをログイン画面に入力してください。</p>
        <p>この認証コードの有効期限は {{ $otpExpires }} です。</p>
        <p>お客様がこのリクエストを行っていない場合、貴社の管理者までお問い合わせください。</p>
        <p>&nbsp;</p>
    </div>
@endsection
