@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>※このメールは自動的に配信しております。<br>
            {{$admin_name}}  様<br><br>
            Shachihata Cloudカスタマーサポートです。<br>
            この度は、弊社サービスをご利用頂き大変ありがとうございます。<br><br>

            下記のリンクから管理者としてログインを行い、<br>
            「BOX自動保管」メニューから再度設定を行ってください。<br>
            https://app.shachihata.com/admin<br><br>
            BOX自動保管の設定方法<br>
            https://help.dstmp.com/help/box-enabled-auto-storage/<br>
        </p>

        <p>&nbsp;</p>
    </div>
@endsection
 