@extends('../layouts_plain.email')

@section('content')
{{$title}}　のセキュリティコード通知します。

セキュリティコード：{{$access_code}}

@endsection