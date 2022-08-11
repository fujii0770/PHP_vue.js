<!DOCTYPE html>
<html lang="ja">
<head>
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
{{--<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto+Sans+JP:300,400,500,700,900">--}}
<!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ asset('/css/bootstrap.min.css') }}">
    <!-- jQuery library -->
    <script src="{{ asset('/js/libs/jquery/3.4.1/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('/js/libs/bootstrap/4.0.0/bootstrap.min.js') }}"></script>
{{--<script src="https://unpkg.com/babel-standalone@6.15.0/babel.min.js"></script>--}}
    <title>@yield('title', config('app.name'))</title>
    <!-- Styles -->
    <style type="text/css">
        html,body{
            height: 100%;
            width: 100%;
        }
        .page{
            height: calc(100% - 80px);
        }
        .content{
            /*height:calc(100% - 40px);*/
            height:100%;
            overflow: auto;/*PAC_5-2572 右側でスクロール出来る*/
            /*PAC_5-2572 追加*/
            background-image:linear-gradient(to bottom,white 0%,lightblue 100%);
            background-image:-webkit-linear-gradient(top,white,lightblue);
            background-image:-ms-linear-gradient(top,white 0%,lightblue 100%);
            background-image:-o-linear-gradient(top,white,lightblue);
            background-image:-moz-linear-gradient(top,white,lightblue);
            -ms-filter:"progid:DXImageTransform.Microsoft.gradient (GradientType=0, startColorstr=white, endColorstr=lightblue)";/*IE8+*/
            filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0 ,startColorstr=white, endColorstr=lightblue);
            /*PAC_5-2572 追加end*/
        }
        .footer{
            width:100%;
            height:80px;
            background-color:#00873c;
            position:fixed;
            bottom:0;
            color:#fff;
        }
        .footer a{
            text-decoration:none;
            color:#fff;
        }
        .row-panel{
            display: inline-block;
        }
        .footer-left{
            float:left;
            padding-left:30px;
            padding-top:20px;
        }
        .footer-right{
            float:right;
            padding-right:30px;
            padding-top:20px;
        }
        .content-left{
            width:50%;
            /*height:100%;/*PAC_5-2690 削除 */
            float:left;
            text-align: center;
            padding-bottom:300px;/*PAC_5-2690 追加 */
            margin: 0 auto;
            /*PAC_5-2572 追加*/
            background:#FFF;
            /*PAC_5-2572 end*/
        }
        .notice{
            background-color: #000;
            color:#fff;
            width:100%;
            /*height:15%;*/
            max-height:20%;
            overflow: auto;
        }
        .panel-login{
            text-align: center;
            padding-top: 5vh;
            width: 100%;
            margin: auto;
            /*overflow: auto;*/
        }
        #loginbtn{
            margin-top:5%;
            margin-bottom:3%;
            color:#fff;
            background-color:#449d44;
            border-color: #398439;
            border-radius:10px 10px 10px 10px;
            width: 67%;
            max-width:345px;
            height:65px;
            text-align: center;
            cursor: pointer;
        }
        #loginbtn.progress-btn {
            -webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
            line-height: 20px;
            -webkit-box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .15);
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .15);
            animation: progress-bar-stripes 2s linear infinite;
            background-image: linear-gradient(45deg, rgba(255, 255, 255, .15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%, transparent 75%, transparent);
            -webkit-background-size: 40px 40px;
            background-size: 40px 40px;
        }
        .content-right{
            text-align: center;
            width:50%;
            /*PAC_5-2572 削除*/
            /*background-image:linear-gradient(to bottom,white 0%,lightblue 100%);
            background-image:-webkit-linear-gradient(top,white,lightblue);
            background-image:-ms-linear-gradient(top,white 0%,lightblue 100%);
            background-image:-o-linear-gradient(top,white,lightblue);
            background-image:-moz-linear-gradient(top,white,lightblue);
            -ms-filter:"progid:DXImageTransform.Microsoft.gradient (GradientType=0, startColorstr=white, endColorstr=lightblue)";/*IE8+*/
            /*filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0 ,startColorstr=white, endColorstr=lightblue);*/
            /*PAC_5-2572 削除end*/
            /*height:100%;*/
            padding-top:2.8%;
            /*padding-top:5%;*/
        }
        .font1{
            color: #0984e3;
            font-size: 3rem;
            font-family: "Yu Gothic Medium";
            padding-top:20px;
        }
        .font2{
            color: #0984e3;
            font-size: 3rem;
            font-family: "Yu Gothic Medium";
        }
        .font3{
            color: #0984e3;
            font-size: 3rem;
            font-family: "Yu Gothic Medium";
        }
        /*.font3{
            font-size: 1rem;
            font-family: "Yu Gothic Medium";
            margin-bottom:20px;
        }*/
        .full-weight{
            width:100%;
        }
        .btn-default{
            border: 1px solid #dee2e6;
        }
        .input-default{
            height: 40px;
            background-color:#f0f0f0;
            border:1px solid #dcdcdc;
            border-radius:5px 5px 5px 5px;
        }
        .login-panel{
            width:50%;
            text-align: left;
            margin:auto;
        }
        #loginform{
            width:60%;
            margin:auto;
        }
        @media screen and (max-width: 960px) {
            .content-right{
                display: none;
            }
            #link{
                display: none;
            }
            .content-left{
                width:100%;
            }
        }
        /* IE10以上 cssを設定 */
        @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
            .logo {
                width: 85px;
                height: 122px;
            }
            .show {
                width: 402px;
                height: 226px;
            }
            .font1 {
                color: #0984e3;
                font-size: 2rem;
                font-family: "Yu Gothic Medium";
                padding-top: 15px;
            }
            .font2 {
                color: #0984e3;
                font-size: 2rem;
                font-family: "Yu Gothic Medium";
                letter-spacing: 0px;
            }
            .font3 {
                color: #0984e3;
                font-size: 2rem;
                font-family: "Yu Gothic Medium";
                letter-spacing: 0px;
            }
            /*.font3 {
                font-size: 1rem;
                font-family: "Yu Gothic Medium";
                margin-bottom: 15px;
            }*/
        }
    </style>
    <script>
        {{--    PAC_5-883BEGIN ログイン画面のパスワード非表示/表示 切替え    --}}
        function showPwd(ck){
            if (ck.checked){
                $("#password").attr("type","text");
            }else {
                $("#password").attr("type","password");
            }
        }
        {{--    PAC_5-883END ログイン画面のパスワード非表示/表示 切替え    --}}
        var isLogged = @php
            $check = Auth::check();
            if ($check) {
                if (Session::has('viaRemember')) {
                    $viaRemember = Session::get('viaRemember');
                } else {
                    $viaRemember = Auth::viaRemember();
                    Session::put('viaRemember', $viaRemember);
                }
                $viaRemember = $viaRemember || Session::get('loginWithRememberChecked');
                echo $viaRemember ? 1 : 0;
            } else {
                echo 0;
            }
        @endphp;

        window.onload=function(){
            if(isLogged == 1){
                window.location.href = '{{url("/")}}/';
                return false;
            }
            // notice get
            var isExists;
            var noticeTxt = "";
            @if(file_exists("notice.txt"))
                noticeTxt = {!! json_encode(file_get_contents("notice.txt")) !!};
                isExists = 1;
            @else
                noticeTxt = "";
                isExists = 0;
            @endif
            // title comment
            if(isExists == 1){
                if(noticeTxt.search("\r\n") > 0){
                    // windows
                    var title = noticeTxt.substr(0,noticeTxt.search("\r\n"));
                    var comment = noticeTxt.substr(noticeTxt.search("\r\n")+1).replace(/\r\n/g,"<br>");
                }else{
                    // linux
                    var title = noticeTxt.substr(0,noticeTxt.search("\n"));
                    var comment = noticeTxt.substr(noticeTxt.search("\n")+1).replace(/\n/g,"<br>");
                }
                $('#notice-title').html(title);
                $('#notice-comment').html(comment);
            }else{
                $('#notice-title').html('');
                $('#notice-comment').html('');
            }
            // ログイン event
            $('#loginform').submit(function () {
                //$('#loginbtn').addClass('progress-btn').val('ログイン処理中');
                $('#loginbtn').addClass('progress-btn').val('ログイン処理中').attr('disabled',true);
                var username = $("#username").val();
                var password = $("#password").val();
                var remember = $("#remember").prop('checked') ? '1' : '0';
                $.ajax({
                    type: 'POST',
                    url: '{{url('/login')}}',
                    dataType: "json",
                    data: {email: username, password: password, remember:remember},
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data, textStatus, jqXHR){
                        //$('#loginbtn').removeClass('progress-btn').val('ログイン');
                        $('#loginbtn').removeClass('progress-btn').val('ログイン');
                        if (jqXHR.status === 200) {
                            window.location.href = '{{url("/")}}/';
                        } else if (jqXHR.status === 205) {
                            $('#loginbtn').removeAttr('disabled');
                            $("#modalWithBtnAndTitle .title").html('トライアル期間が終了いたしました');
                            $("#modalWithBtnAndTitle .message").html('この度はShachihata Cloud をお試しいただき、ありがとうございました。<br>' +
                                '現在、トライアル期間が終了しログインを停止しております。<br>' +
                                '「本契約」へお進みいただく場合は下記ボタンをクリックください。<br>' +
                                '「お問合せ」についても、同ページからご案内しております。');
                            $("#modalWithBtnAndTitle .message-index").html('シヤチハタ株式会社');
                            $("#modalWithBtnAndTitle").modal();
                        } else if (jqXHR.status === 203) {
                            $('#loginbtn').removeAttr('disabled');
                            $("#modalError .message").html('メールアドレス、又はパスワードが正しくありません');
                            $("#modalError").modal();
                        } else {
                            $('#loginbtn').removeAttr('disabled');
                            $("#modalError .message").html(data.message || '異常が発生しています。問い合わせてください');
                            $("#modalError").modal();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        //$('#loginbtn').removeClass('progress-btn').val('ログイン');
                        $('#loginbtn').removeClass('progress-btn').val('ログイン').removeAttr('disabled');
                        let resJson = jqXHR.responseJSON || null;
                        if(jqXHR.status === 403) {
                            $("#modalError .message").html(resJson.message || '異常が発生しています。問い合わせてください');
                            $("#modalError").modal();
                        }else if (jqXHR.status === 205) {
                            $("#modalWithBtnAndTitle .title").html('トライアル期間が終了いたしました');
                            $("#modalWithBtnAndTitle .message").html('この度はShachihata Cloud をお試しいただき、ありがとうございました。<br>' +
                                '現在、トライアル期間が終了しログインを停止しております。<br>' +
                                '「本契約」へお進みいただく場合は下記ボタンをクリックください。<br>' +
                                '「お問合せ」についても、同ページからご案内しております。');
                            $("#modalWithBtnAndTitle .message-index").html('シヤチハタ株式会社');
                            $("#modalWithBtnAndTitle").modal();
                        }else if(jqXHR.status === 429){
                            $("#modalError .message").html(resJson.errors.email[0] || '異常が発生しています。問い合わせてください');
                            $("#modalError").modal();
                        }else{
                            $("#modalError .message").html('異常が発生しています。問い合わせてください');
                            $("#modalError").modal();
                        }
                    }
                });
                return false;
            });

            let panelLogin = document.getElementsByClassName('panel-login')[0];
            if ($('#notice-title').html()) {
                let notice = document.getElementsByClassName('notice')[0];
                let noticeHeight = window.getComputedStyle(notice, null).getPropertyValue('height').replace('px', '');
                let contentLeft = document.getElementsByClassName('content-left')[0];
                let contentLeftHeight = window.getComputedStyle(contentLeft, null).getPropertyValue('height').replace('px', '');
                let heightPercen = Math.round(noticeHeight / contentLeftHeight * 100);
                panelLogin.style.height = 100 - heightPercen + '%';
            } else {
                panelLogin.style.height = 100 + '%';
            }
        };
    </script>
</head>
<body style="overflow: hidden;margin:0px;">
<div class="page">
    <div class="content">
        <div class="content-left row-panel">
            <div class="notice">
                <div id="notice-title" style="font-size: 1.4rem;"></div>
                <div id="notice-comment" class="notice-body">
                </div>
            </div>
            <div class="panel-login">
                <div>
                    <img class="logo" src="{{ asset('images/logo_shachihata.png') }}">
                </div>
                <form id="loginform">
                    @csrf
                    <div class="login-panel">
                        <div class="full-weight" style="text-align: left;margin-top: 30px;margin-bottom:10px;">ユーザ名</div>
                        <input type="text" class="full-weight input-default" name="email" id="username" required style="text-transform:lowercase;">
                        <div style="text-align: left;margin-top: 30px;margin-bottom:10px;">パスワード</div>
                        <input type="password" class="full-weight input-default" name="password" id="password" required> <br />
                        {{--    PAC_5-883 表示パスワードを追加する                    --}}
                        <input type="checkbox" style="margin-top:20px;cursor:pointer;" name="showPassword" id="showPassword" onclick="showPwd(this)">
                        <label for="showPassword" style="cursor:pointer;">パスワードを表示</label> <br />
                        <input type="checkbox" style="margin-top:20px;cursor:pointer;" name="remember" id="remember" value="1">
                        <label for="remember" style="cursor:pointer;">ログイン状態を保存する</label> <br />
                    </div>
                    <div>
                        <input type="submit" id="loginbtn" value="ログイン"> <br />
                        <a href="{{url('/password-code')}}" style="color:black;"><u>パスワード設定</u></a> <br />
                    </div>
                </form>
            </div>
        </div>
        <div class="content-right row-panel">
            <div>
                <img class="logo" src="{{ asset('images/logo_shachihata.png') }}">
                <div class="font1"><b>時代と共に進化し続ける</b></div>
                <div class="font2"><b>シヤチハタブランドをデジタルでも</b></div>
            </div>
            <div style="height:50%;">
                <img class="logo" src="{{ asset('images/show.png') }}">
                <div style="width:300px;height:40px;line-height:40px;color:#0984e3;background-color:#ffffff;border:1px solid #0984e3;border-radius: 5px 5px 5px 5px;margin:20px auto;">
                    <a style="text-decoration:none;" href="https://dstmp.shachihata.co.jp/" target="_blank"><b>Shachihata Cloudトップページへ</b></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        {{--<div class="row-panel footer-left">--}}
        {{--vsersion {{env('VERSION','00.00')}}--}}
        {{--</div>--}}
        <div class="row-panel footer-right">
            <div id="link">
                <a href="https://help.dstmp.com/" target="_blank">ヘルプサイト</a>　/　
                <a href="https://www.shachihata.co.jp/policy/index.php" target="_blank">プライバシーポリシー</a>　/　
                <a href="mailto:pa-cloud-support@ex.shachihata.co.jp" target="_blank">お問い合わせ</a>
            </div>
            <div style="text-align: right;">
                <a href="http://www.shachihata.co.jp" target="_blank">©2020&nbsp;Shachihata Inc.</a>
            </div>
        </div>
    </div>
</div>
@includeif('Login.modal')
@includeif('Login.btnModal')
</body>
</html>
