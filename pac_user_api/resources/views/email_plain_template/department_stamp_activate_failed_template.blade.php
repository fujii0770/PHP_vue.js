@extends('../layouts_plain.email')

@section('content')
システム管理者様

部署名入り日付印の有効化処理において、DB更新エラーが発生しました
------------------
失敗した印面IDは以下の通りです。
------------------
{{stampIdStr}}
统计した印面IDは以下の通りです。

成功総数：
{{$successCount件}}
失敗総数：
{{$failureCount件}}
------------------
@endsection