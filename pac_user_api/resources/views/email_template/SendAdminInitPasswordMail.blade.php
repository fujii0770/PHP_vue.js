@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        <p>いつもShachihata Cloudをご利用いただきありがとうございます。</p>
        <p>ご利用のShachihata Cloud管理者アカウントに対して、初期パスワードを発行しました。</p><br>
        <p>パスワード： {{ $password }} </p>
        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tbody>
            <tr>
                <td style="width:20%"></td>
                    <!-- trial company does not support SAML -->
                <td class="m_-6849224559476667371link-button" style="min-width: 140px;
    border: 1px solid whitesmoke;
    border-radius: 8px;
    text-align: center;
    vertical-align: middle;
    background-color: #107fcd;"><a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px;" href="{{ str_replace('app-api','admin',config('app.url')) }}" class="m_-6849224559476667371link-button" target="_blank" data-saferedirecturl="">ログイン画面に移動</a></td>
                <td style="width:20%"></td>
            </tr>
            </tbody>
        </table>
        <p>お客様がこのリクエストを行っていない場合、このままこのメールを削除してください。</p>
        <p>他人が不正にアカウントにアクセスしていると思われる場合は、</p>
        <p>Shachihata Cloudの設定ページで、ただちにパスワードを変更してください。</p>
        <p>&nbsp;</p>
    </div>
@endsection
