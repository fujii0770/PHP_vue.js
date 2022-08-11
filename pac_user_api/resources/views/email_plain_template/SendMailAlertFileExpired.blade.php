@extends('../layouts_plain.email')

@section('content')
まもなく、以下の回覧文書の保存期間が終了いたします。
期間終了後は表示・保存ができなくなりますので、ご注意ください。
対象の回覧文書：
@foreach ($circularDocuments as $circularDocument)
    @foreach ($circularDocument['files'] as $file)
        ・送信日時: {{ ($file['create_at'])}}, {{$circularDocument['subject']?'件名: '.$circularDocument['subject'].', ':''}}ファイル名: {{ $file['file_name'] }}
    @endforeach
@endforeach


※この電子メールの内容を他の人と共有しないでください。

ログイン画面に移動 : {{isset($env_app_url)?$env_app_url:config('app.new_app_url')}}

@endsection
 
