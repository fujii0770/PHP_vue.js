@extends('../layouts_plain.email')

@section('content')
いつもShachihata Cloudをご利用いただきありがとうございます。
ご利用のShachihata Cloudに対して、登録外のIPアドレスからログインが行われました。

IPアドレス：{{ $ipAddress }}
ユーザー：{{ $user }}

@endsection
