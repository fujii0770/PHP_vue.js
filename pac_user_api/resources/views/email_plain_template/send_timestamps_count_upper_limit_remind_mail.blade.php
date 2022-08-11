@extends('../layouts_plain.email')

@section('content')
{{$company_name}}
{{$admin_name}} 様

いつもShachihata Cloudをご利用いただきありがとうございます。
ご利用のShachihata Cloudに対して
タイムスタンプ発行数が上限に達しました。

契約数を契約サイトにてご確認お願いいたします。
また、追加購入の場合は契約サイトからご購入ください。
{{$cloud_link}}
@endsection
