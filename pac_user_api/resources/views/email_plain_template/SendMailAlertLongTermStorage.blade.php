@extends('../layouts_plain.email')

@section('content')
まもなく、利用可能な長期保管ディスク容量をオーバーします。
不要になった回覧文書を削除してください。

使用ディスク容量：
{{$current_long_term_storage_size }}

ディスク使用率：
{{$current_long_term_storage_percent}}

※この電子メールの内容を他の人と共有しないでください。

@endsection
