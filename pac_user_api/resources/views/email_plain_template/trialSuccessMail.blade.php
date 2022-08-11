@extends('../layouts_plain.email')

@section('content')
    Shachihata Cloud30日間トライアルにお申込いただき
    誠にありがとうございます。
    初期パスワードを発行いたしましたので24時間以内にログインをお試しください。

    パスワード： {{$password}}

    また、ログイン後の右上のアイコンより、管理者アカウントへログインしていただけます。
    ※初期パスワードは同じです。

    ログインする： {{ str_replace('app-api','app',config('app.url')) }}





    ▼ 「初回設定マニュアル」
    https://help.dstmp.com/help/firstmanual-b/
    ▼ 管理者向けヘルプサイト
    https://help.dstmp.com/scloud/business/admin/
    ▼ 利用者向けヘルプサイト
    https://help.dstmp.com/scloud/business/user/
    ▼ ヘルプサイト
    https://help.dstmp.com/

    今回のトライアルは下記の内容で承りました。
    ----------------------------------------------------------------------------------------------------------------------------------------------------
    対象商品 : Shachihata Cloud トライアル
    会社名 : {{ $nickname }}
    会社名フリガナ : {{ $kananame }}
    電話番号 : {{ $telno }}
    業種 : {{ $industry }}
    部署・役職名 : {{ $group }} {{ $post }}
    お名前 : {{ $familyname }} {{ $givenname }}
    お名前フリガナ： {{ $familynameKana }} {{ $givennameKana }}
    担当者の電話番号 : {{ $telno2 }}
    メールアドレス : {{ $email }}
    あなたのお立場をお聞かせください : {{ $position }}

    @if($simple_user_flag)
        簡易利用者情報
        @foreach($success_users as $success_user)
            お名前 : {{ $success_user['simple_user_name1'] }} {{ $success_user['simple_user_name2'] }}
            メールアドレス : {{ $success_user['simple_user_email'] }}
            状態 : {{ $success_user['simple_user_status'] }}
        @endforeach

        @foreach($failed_users as $failed_user)
            お名前 : {{ $failed_user['simple_user_name1'] }} {{ $failed_user['simple_user_name2'] }}
            メールアドレス : {{ $failed_user['simple_user_email'] }}
            状態 : {{ $failed_user['simple_user_status_repeat'] }}@if($failed_user['simple_user_status_repeat']);@endif{{ $failed_user['simple_user_status_domain'] }}
        @endforeach
    @endif

    パソコン決裁Cloudのご利用状況 : {{ $is_inuse }}
    解決したい課題 : {{ $task }}
    導入予定時期 : {{ $pretiming }}
    印鑑数 : {{ $maxstamps }}
    利用ドメイン : {{ $domains }}
    メルマガ配信希望 :{{ $mailMagazine }}
    ----------------------------------------------------------------------------------------------------------------------------------------------------

@endsection