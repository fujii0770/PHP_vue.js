@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">
        @if($company_name)
        <p>対象企業：{{ $company_name }}<br>
        対象ID：{{ $user_id }}</p>
        @endif
        <p>いつもShachihata Cloudをご利用いただきありがとうござい<wbr>ます。</p>
        <p>
            ご利用のShachihata Cloudアカウントに対して、<wbr>初期パスワードを発行いたしました。<br>
        </p>
        <p>パスワード：{{ $password }}</p>
        <p>&nbsp;</p>
        <table style="width: 100%; text-align: center">
            <tbody>
            <tr>
                <td style="width:20%"></td>
                <td style="width:60%;margin: 0 auto;  background-color: #107fcd; min-width: 140px; padding: 8px 20px; border-radius: 8px; text-align: center; text-decoration: none;">
                    @if(isset($login_url) && $login_url)
                        <a style="color: white; " href="{{ $login_url }}">{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($login_url)}}</a>
                    @else
                        <a style="color: white; " href="{{ config('app.new_app_url') }}">ログイン画面に移動</a>
                    @endif
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
