<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('/css/libs/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/mfa.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/validationEngine.css') }}">
</head>
<body>
<header class="header-login" {{$background_color?'style=background-color:#'.$background_color.'':''}}>
    <a class="navbar-brand" href="/app/">
        <img src="{{$logo_file_data?'data:image/png;base64, '.$logo_file_data:asset('images/logo.png')}}">
    </a>
</header>
<div id="main-contents">
    <div id="user-new-login">
        <div id="rightbox" style="padding-top: 24px !important;">
            <div class="side-login">
                <form method="get" action="{{url(config('app.saml_url_prefix').'/'.$url_domain_id)}}">
                    <input style="background-color:#{{$background_color?$background_color:'107FCD'}}; border-color:transparent" type="submit" class="btn btn-primary form-control" id="sso-loginbtn" tabindex="4" value="ログイン" />
                </form>
            </div>

            {{--<div class="guide-signup">
            <span>
                アカウントをお持ちでない場合
                <br>
                <a href="#" target="_self" tabindex="4">Starter(無料版)登録</a>
            </span>
            </div>--}}
        </div>
        <div class="footer" {{$background_color?'style=background-color:#'.$background_color.'':''}}>
            <!-- Version 1.7.16.246-e967d33f5e -->
            <div class="footer-text" {{$color?'style=color:#'.$color.'':''}}>Version 1.7.16.246</div>
            <div class="footer-text"><a href="http://www.shachihata.co.jp" target="_blank" {{$color?'style=color:#'.$color.'':''}}>©2017 Shachihata Inc.</a></div>
            <div class="footer-text"><a href="http://www.shachihata.co.jp/policy/index.php" target="_blank" {{$color?'style=color:#'.$color.'':''}}>プライバシーポリシー</a></div>
            <div class="footer-text"><a href="mailto:pa-cloud-support@ex.shachihata.co.jp" target="_blank" {{$color?'style=color:#'.$color.'':''}}>問い合わせ</a></div>
            <div class="footer-text"><a href="https://help.dstmp.com/admin-top/" target="_blank" {{$color?'style=color:#'.$color.'':''}}>ヘルプ</a></div>
        </div>
    </div>
</div>
<script src="{{ asset('js/libs/jquery/3.4.1/jquery-3.4.1.min.js') }}"></script>
<script src="{{ asset('js/libs/bootstrap/4.0.0/bootstrap.min.js') }}"></script>
</body>
</html>
