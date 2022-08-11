@extends('../layouts.email')

@section('content')
  <div style="padding:16px 8px;background-color:white">
    <p>まもなく、利用可能なディスク容量をオーバーします。<br/>
      不要になった回覧文書を削除してください。</p>
    <br>
    <p>使用ディスク容量：<br>
      {{$current_storage_size}}MB
    </p>
    <br>
    <p>ディスク使用率：<br>
      {{$current_storage_percent}}%
    </p>
    <br>
    <p>※この電子メールの内容を他の人と共有しないでください。</p>
    <p>&nbsp;</p>
  </div>
@endsection
 