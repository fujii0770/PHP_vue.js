@extends('../layouts.email')

@section('content')
    <div style="padding:16px 8px;background-color:white">

        <p>このメールは自動送信されたメールです。</p>
        <p>
            以下の内容でトライアルがありましたが、<wbr>開始できませんでした。<br>
            --------------------------------------------------------------------------<br>
            申込日時 : {{ $nowdete }}<br>
            対象商品 : Shachihata Cloud トライアル<br>
            会社名 : {{ $nickname }}<br>
            会社名フリガナ : {{ $kananame }}<br>
            @if(isset($agcode) && $agcode)
            代理店コード : {{ $agcode }}<br>
            @else
            @endif
            @if(isset($agcouponcode) && $agcouponcode)
            代理店クーポンコード : {{ $agcouponcode }}<br>
            @else
            @endif
            電話番号 : {{ $telno }}<br>
            業種 : {{ $industry }}<br>
            部署・役職名 : {{ $group }} {{ $post }}<br>
            お名前 : {{ $familyname }} {{ $givenname }}<br>
            お名前フリガナ： {{ $familynameKana }} {{ $givennameKana }}<br>
            担当者の電話番号 : {{ $telno2 }}<br>
            メールアドレス : {{ $email }}<br>
            あなたのお立場をお聞かせください : {{ $position }}<br><br>
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
            ご意見・ご要望 : {{ $question }}<br>
            印鑑数 : {{ $maxstamps }}<br>
            利用ドメイン : {{ $domains }}<br><br>
            メルマガ配信希望 : {{ $mailMagazine }}<br>
            --------------------------------------------------------------------------
        </p>
    </div>
@endsection



