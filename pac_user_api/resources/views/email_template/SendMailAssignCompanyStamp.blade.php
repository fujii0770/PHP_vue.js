@extends('../layouts.email')

@section('content')
  <div style="padding:16px 8px;background-color:white">
    <p>Shachihata Cloudをご利用いただきありがとうございます。<br>お申し込みいただいておりました共通印の準備が整いました。<br>Shachihata Cloudの管理者サイトにログインし、共通印設定画面でご確認ください。</p>

    <table style="width: 100%; text-align: center">
      <tbody>
      <tr>
        <td style="width:20%"></td>
        <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">
            <!-- Mail to Administrator -->
          <a style="color: white; " href="{{ str_replace('app-api','admin',config('app.url')) }}">ログイン画面に移動</a>
        </td>
        <td style="width:20%"></td>
      </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
  </div>
@endsection
 