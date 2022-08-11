
@extends('../layouts_plain.email')

@section('content')
※このメールは自動的に配信しております。
{{$admin_name}}  様

Shachihata Cloudカスタマーサポートです。
この度は、弊社サービスをご利用頂き大変ありがとうございます。

BOX自動保管に関するトークンの自動更新が失敗しました。
下記のリンクから管理者としてログインを行い、
「BOX自動保管」メニューから再度設定を行ってください。
https://app.shachihata.com/admin

BOX自動保管の設定方法
https://help.dstmp.com/help/box-enabled-auto-storage/

@endsection