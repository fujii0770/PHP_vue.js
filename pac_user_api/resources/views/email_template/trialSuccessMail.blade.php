@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">

        <p>
            Shachihata Cloud30日間トライアルにお申込いただき<br>
            誠にありがとうございます。<br>
            初期パスワードを発行いたしましたので24時間以内にログインをお試しください。<br><br>
        </p>

        <p>
            パスワード：{{$password}}<br>
        </p>

        <p>
            また、ログイン後の右上のアイコンより、管理者アカウントへログインしていただけます。<br>
            ※初期パスワードは同じです。<br>
        </p>
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
    background-color: #107fcd;"><a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px;" href="{{ str_replace('app-api','app',config('app.url')) }}" class="m_-6849224559476667371link-button" target="_blank" data-saferedirecturl="">ログインする</a></td>
                <td style="width:20%"></td>
            </tr>
            </tbody>
        </table>
        <br><br><br><br><br>
        <p>
            ▼ 「初回設定マニュアル」<br>
            https://help.dstmp.com/help/firstmanual-b/<br>
            ▼ 管理者向けヘルプサイト<br>
            https://help.dstmp.com/scloud/business/admin/<br>
            ▼ 利用者向けヘルプサイト<br>
            https://help.dstmp.com/scloud/business/user/<br>
            ▼ ヘルプサイト<br>
            https://help.dstmp.com/<br>
        </p>
        <p>
            今回のトライアルは下記の内容で承りました。<br>
            --------------------------------------------------------------------------<br>
            対象商品 : Shachihata Cloud トライアル<br>
            会社名 : {{ $nickname }}<br>
            会社名フリガナ : {{ $kananame }}<br>
            電話番号 : {{ $telno }}<br>
            業種 : {{ $industry }}<br>
            部署・役職名 : {{ $group }} {{ $post }}<br>
            お名前 : {{ $familyname }} {{ $givenname }}<br>
            お名前フリガナ： {{ $familynameKana }} {{ $givennameKana }}<br>
            担当者の電話番号 : {{ $telno2 }}<br>
            メールアドレス : {{ $email }}<br>
            あなたのお立場をお聞かせください : {{ $position }}<br>
            @if($simple_user_flag)
                簡易利用者情報<br>
                @foreach($success_users as $success_user)
                    お名前 : {{ $success_user['simple_user_name1'] }} {{ $success_user['simple_user_name2'] }}<br>
                    メールアドレス : {{ $success_user['simple_user_email'] }}<br>
                    状態 : {{ $success_user['simple_user_status'] }}<br>
                @endforeach
                <br>
                @foreach($failed_users as $failed_user)
                    お名前 : {{ $failed_user['simple_user_name1'] }} {{ $failed_user['simple_user_name2'] }}<br>
                    メールアドレス : {{ $failed_user['simple_user_email'] }}<br>
                    状態 : {{ $failed_user['simple_user_status_repeat'] }}@if($failed_user['simple_user_status_repeat']);@endif{{ $failed_user['simple_user_status_domain'] }}<br>
                @endforeach
                <br>
            @endif
            パソコン決裁Cloudのご利用状況 : {{ $is_inuse }}<br>
            解決したい課題 : {{ $task }}<br>
            導入予定時期 : {{ $pretiming }}<br>
            印鑑数 : {{ $maxstamps }}<br>
            利用ドメイン : {{ $domains }}<br>
            メルマガ配信希望 : {{ $mailMagazine }}<br>
            --------------------------------------------------------------------------<br>
        </p>
    </div>
@endsection