@extends('../layouts.email')

@section('content')
<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="padding:16px 8px;background-color:white">
        <p>いつもShachihata Cloudをご利用いただきありがとうございます。</p>
        <p>お申し込みいただいておりました。{{$userName}}さんの部署名入り日付印の準備が整いました。</p>
    </div>
</div>
@endsection