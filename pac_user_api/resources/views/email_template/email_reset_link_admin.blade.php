@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>いつもShachihata Cloudをご利用いただきありがとうござい<wbr>ます。</p>
        <p>
            ご利用の管理者アカウントに対して、<wbr>初期パスワードを発行いたしました。<br>
        </p>
        <p>パスワード：{{ $password }}</p>
        <p>&nbsp;</p>
        <table style="width: 100%; text-align: center">
            <tbody>
            <tr>
                <td style="width:20%"></td>
                <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">>
                    <!-- Send Mail to Admin -->
                    <a style="color: white; " href="{{ str_replace('app-api','admin',config('app.url')) }}">ログイン画面に移動</a>
                </td>
                <td style="width:20%"></td>
            </tr>
            </tbody>
        </table>
        <p>
            お客様がこのリクエストを行っていない場合、<wbr>このままこのメールを削除してください。<br>
            他人が不正にアカウントにアクセスしていると思われる場合は、<br>
            Shachihata Cloudの設定ページで、<wbr>ただちにパスワードを変更してください。
        </p>
    </div>
@endsection
