@extends('../layouts_plain.email')

@section('content')
{{$company_name}}
{{$admin_name}} 様

いつもShachihata Cloudをご利用いただきありがとうございます。
ご利用のShachihata Cloudに対して
タイムスタンプの残り回数が {{$timestamps_count}} 回以下になりました。

契約数を契約サイトにてご確認お願いいたします。
{{$cloud_link}}
@endsection
