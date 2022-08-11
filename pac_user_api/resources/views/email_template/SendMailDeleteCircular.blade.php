@extends('../layouts.email')

@section('content')
  <div style="padding:16px 8px;background-color:white">
    <p>管理者により以下の回覧が削除されました。<br>※申請日時 - 件名(ファイル名)</p>
    <p>
    ・{{  $deleteTime }} - {{$title}} ({{ $fileName }})<br>
    </p>

    <table style="width: 100%; text-align: center">
      <tbody>
      <tr>
        <td style="width:20%"></td>
        <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">
            @if(isset($url_domain_id) && $url_domain_id)
                <a style="color: white; " href="{{ config('app.new_app_url').'/'.config('app.saml_url_prefix').'/'.$url_domain_id }}">SAML機能でログイン</a>
            @else
                <a style="color: white; " href="{{ config('app.new_app_url') }}">ログイン画面に移動</a>
            @endif
        </td>
        <td style="width:20%"></td>
      </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
  </div>
@endsection
 