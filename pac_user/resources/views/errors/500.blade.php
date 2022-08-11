<!DOCTYPE html>
<html lang="ja">

<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ asset('/css/libs/bootstrap.min.css') }}">

    <title>@yield('title', config('app.name'))</title>
    <!-- Styles -->
    <style type="text/css">
        html,
        body {
            height: 100%;
            width: 100%;
        }

        .page {
            height: calc(100%);
        }

        .footer {
            width: 100%;
            height: 40px;
            background-color: #0984e3;
            position: fixed;
            bottom: 0;
            color: #fff;
            display: inline-block;
        }

        .footer a {
            text-decoration: none;
            color: #fff;
        }

        .row-panel {
            padding-top: 4px;
        }

        .footer-right {
            float:right;
            padding-right: 4px;
            vertical-align: middle;
            /*   */
        }

        .content {
            text-align: center;
            width: 100%;
            height: 100%;
            padding-top: 1%;
            overflow: auto;
        }

        .font1 {
            color: #0984e3;
            font-size: 2rem;
            font-family: "Yu Gothic Medium";
            padding-top: 20px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .font2 {
            font-size: 1rem;
            font-family: "Yu Gothic Medium";
            padding-left: 20px;
            padding-right: 20px;
        }

        @media screen and (max-width: 960px) {
            .content {
                width: 100%;
            }

            #link {
                display: none;
            }
        }

        /* IE10以上 cssを設定 */
        @media all and (-ms-high-contrast: none),
        (-ms-high-contrast: active) {
            /* .logo {
                width: 85px;
                height: 122px;
            } */

            .font1 {
                color: #0984e3;
                font-size: 2rem;
                font-family: "Yu Gothic Medium";
                margin-top: 15px;
                margin-left:5px;
                margin-right:5px;
            }

            .font2 {
                font-size: 1rem;
                font-family: "Yu Gothic Medium";
                margin-bottom: 15px;
                padding-left:5px;
                padding-right:5px;
            }
        }
    </style>
</head>

<body style="margin:0px;">
    <div class="page">
            <div class="content row-panel" style="height:95%">
                    <div><img src="images/logo_shachihata_circular.png" /></div>
                    <div style="padding-top: 50px;"><img src="images/staff_ozigi.png" /></div>
                    <div class="font1"><b>ただいまアクセスしづらい状況になっております</b></div>
                    <div class="font2">大変申し訳ありませんが、しばらくたってから再度アクセスしてください。</div>
                    <div
                        style="width:300px;height:40px;line-height:40px;background-color:#0984e3;border:1px solid #0984e3;border-radius: 5px 5px 5px 5px;margin:20px auto;">
                        <a style="text-decoration:none;color:#fff;" href="https://dstmp.shachihata.co.jp/"
                            target="_blank"><b>Shachihata Cloudトップページへ</b></a>

                    </div>
            </div>
        <div class="footer">
            <div class="row-panel footer-right">
                    Copyright © 2020&nbsp;Shachihata Inc., All Rights Reserved.
            </div>
        </div>
    </div>
</body>

</html>