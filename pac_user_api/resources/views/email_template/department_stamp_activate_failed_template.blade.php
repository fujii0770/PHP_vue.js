@extends('../layouts.email')

@section('content')
<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="padding:16px 8px;background-color:white">
        <p>システム管理者様</p>
        <br>
        <p>部署名入り日付印の有効化処理において、DB更新エラーが発生しました</p>
        <p>------------------</p>
        <p>失敗した印面IDは以下の通りです。</p>
        <p>------------------</p>
        <p>{{stampIdStr}}</p>
        <p>统计した印面IDは以下の通りです。</p>
        <dl>
            <dt>成功総数：</dt>
            <dd>{{$successCount件}}</dd>
            <dt>失敗総数：</dt>
            <dd>{{$failureCount件}}</dd>
        </dl>
        <p>------------------</p>
    </div>
</div>
@endsection