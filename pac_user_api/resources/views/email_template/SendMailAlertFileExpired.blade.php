@extends('../layouts.email')

@section('content')
  <div style="padding:16px 8px;background-color:white">
    <p>まもなく、以下の回覧文書の保存期間が終了いたします。<br/>期間終了後は表示・保存ができなくなりますので、ご注意ください。</p>
    <br/>
    <p>対象の回覧文書：</p>
    @foreach ($circularDocuments as $circularDocument)
        @foreach ($circularDocument['files'] as $file)
          <p>・送信日時: {{ ($file['create_at'])}}, {{$circularDocument['subject']?'件名: '.$circularDocument['subject'].', ':''}}ファイル名: {{ $file['file_name'] }}</p>
        @endforeach
    @endforeach
    <br/>
    <br/>
    <p>※この電子メールの内容を他の人と共有しないでください。</p>
    <table style="width: 100%; text-align: center">
      <tbody>
      <tr>
        <td style="width:20%"></td>
        <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">
          <a style="color: white; " href="{{isset($env_app_url)?$env_app_url:config('app.new_app_url')}}">ログイン画面に移動</a>
        </td>
        <td style="width:20%"></td>
      </tr>
      </tbody>
    </table>
  </div>
  <p></p>
@endsection
 