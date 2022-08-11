@extends('../layouts.email')

@section('content')
  <div style="padding:16px 8px;background-color:white">
    <p>{{ $adminName }} 様 <br />
      Shachihata Cloudの部署情報ダウンロードファイルの作成が完了しましたので、ご連絡致します。
    </p>

    <table style="width: 100%; text-align: center">
      <tbody>
      <tr>
        <td style="width:20%"></td>
        <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">
            <!-- Send Mail to Admin -->
          <a style="color: white; " href="{{ str_replace('app-api','admin',config('app.url')) }}">ログイン画面に移動</a>
        </td>
        <td style="width:20%"></td>
      </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
  </div>
@endsection
 