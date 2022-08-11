@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>いつもShachihata Cloudをご利用いただきありがとうございます。</p>
        <p>ご利用のShachihata Cloudに対して、登録外のIPアドレスからログインが行われました。</p>
        <p>
            IPアドレス：{{ $ipAddress }}<br/>
            ユーザー：{{ $user }}
        </p>

        <p>&nbsp;</p>
    </div>
@endsection
