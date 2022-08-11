@extends('../layouts_plain.email')

@section('content')
回覧文書{{ $title }}のアクセスコードを通知します。

アクセスコード：
{{ $access_code }}
@endsection
