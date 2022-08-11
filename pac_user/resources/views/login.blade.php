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
        <header class="header-login">
            <a class="navbar-brand" href="/app/">
                <img src="/images/logo.png">
            </a>
        </header>
        <div id="main-contents">
            <div id="user-new-login">
                <div id="rightbox" style="padding-top: 24px !important;">
                    <div class="side-login">
                        <form id="loginform" class="loginform">
                            @csrf
                            <label for="email">メールアドレス</label>
                            <input type="text" id="loginform-email" name="email" class="form-control validate[required,custom[email]]" placeholder="email@your.domain" tabindex="1">

                            <input type="button" style="background-color: #107FCD;" class="btn btn-primary form-control" id="login-btn" tabindex="2" value="ログイン">
                        </form>
                    </div>

                    <div class="modal fade" id="edition-panel" tabindex="-1" data-keyboard="false" data-backdrop="static">
                        <div class="modal-dialog">
                            <div class="modal-content" style="width: 600px">
                                <div class="modal-header">
                                    <h5 class="modal-title">ログインエディションの選択</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div style="margin-bottom: 10px;">
                                        ログインするエディションを選択してください。
                                    </div>
                                    <div style="display:flex;justify-content:space-around">
                                        <button class="btn btn-primary btn-lg" id="current-edition-btn" type="button">
                                        </button>
                                        <button class="btn btn-primary btn-lg" id="new-edition-btn" type="button">
                                        </button>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" data-dismiss="modal" id="modal-close-btn" class="btn btn-default">閉じる</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="guide-signup">
                    <span>
                        アカウントをお持ちでない場合
                        <br>
                        <a href="#" target="_self" tabindex="4">Starter(無料版)登録</a>
                    </span>
                    </div>
                </div>
                <div class="footer">
                    <!-- Version 1.7.16.246-e967d33f5e -->
                    <div class="footer-text">Version 1.7.16.246</div>
                    <div class="footer-text"><a href="http://www.shachihata.co.jp" target="_blank">©2017 Shachihata Inc.</a></div>
                    <div class="footer-text"><a href="http://www.shachihata.co.jp/policy/index.php" target="_blank">プライバシーポリシー</a></div>
                    <div class="footer-text"><a href="mailto:pa-cloud-support@ex.shachihata.co.jp" target="_blank">問い合わせ</a></div>
                    <div class="footer-text"><a href="https://help.dstmp.com/admin-top/" target="_blank">ヘルプ</a></div>
                </div>
            </div>
        </div>

        <script src="{{asset('js/libs/jquery/3.4.1/jquery-3.4.1.min.js')}}"></script>
        <script src="{{asset('js/libs/bootstrap/4.0.0/bootstrap.min.js')}}"></script>
        <script src="{{asset('js/libs/validationEngine/jquery.validationEngine.min.js')}}"></script>
        <script src="{{asset('js/libs/languages/jquery.validationEngine-ja.min.js')}}"></script>
        <script src="{{config('app.url')}}/js/pac-user-login-client.js" ></script>
        <script src="{{ config('app.management_api_env') }}/js/pac-id-api-client.js"></script>
        <script>
            var pacIdClient = null, pacUserClient = null;
            var localApiUrl = '{{config('app.url')}}';
            var managementApiUrl = '{{ config('app.management_api_env') }}';
            var newEditionEnv = '{{ config('app.app_server_env') }}';
            var old_system = {{ \App\Utils\UserLoginUtils::OLD_SYSTEM }};
            var new_edition = {{ config('app.edition_flg') }};

            $(document).ready(function() {
                $('#loginform-email').focus();
                $('#loginform-email').keydown(function (e) {
                    if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
                        e.preventDefault();
                        $('#login-btn').click();
                    }
                });
                pacIdClient = new PacIdApiClient(managementApiUrl, '{{ \App\Utils\UserLoginUtils::CLIENT_ID }}', '{{ \App\Utils\UserLoginUtils::CLIENT_SECRET }}');
                // pacUserClient = new PacUserLoginClient(localApiUrl);
                $('#login-btn').click(function (e) {
                    var emailInput = $('#loginform-email').val();
                    var checkOk = checkValidate();
                    if (checkOk) {
                        pacIdClient.execute('users/findByEmail', 'post', {
                            'email': emailInput,
                            'user_auth': '{{ \App\Utils\UserLoginUtils::USER_AUTH }}',
                            'app_env': newEditionEnv //0:AWS,1:K5
                        }, callback);
                        function callback(responseCode, responseBody) {
                            var contract_app = null;
                            var system_name = null;
                            var emptyData =  Object.keys(responseBody.data).length === 0;
                            if (responseBody && responseBody.data && !emptyData) {
                                contract_app = responseBody.data.contract_app;
                                system_name = responseBody.data.system_name;
                            } else {
                                alert('メールアドレスが存在しません');
                                return false;
                            }

                            if(contract_app === {{ \App\Utils\UserLoginUtils::BOTH_SYSTEMS }} || contract_app === new_edition) {
                                var url = `/pwd`;
                                var form = $('<form action="' + url + '" method="post" style="display: none">' +
                                    '<input type="text" name="email" value="' + emailInput + '" />' +
                                    '<input type="hidden" name="_token" id="token" value="'+ `{{ csrf_token() }}` +'" />' +
                                    '</form>');
                                $('body').append(form);
                                form.submit();
                            } else {
                                alert('メールアドレスが存在しません');
                                return false;
                            }

                            /*if (contract_app === null) {
                                return true;
                            {{--} else if (contract_app === {{ \App\Utils\UserLoginUtils::BOTH_SYSTEMS }}) {--}}
                                if (system_name) {
                                    $("#current-edition-btn").html(system_name[0]);
                                    $("#new-edition-btn").html(system_name[1]);
                                    $('#edition-panel').modal('show');
                                    return true;
                                }
                            }
                            else {
                                fwdLogin(contract_app);
                            }*/
                        }
                    }
                });
                /*$('#current-edition-btn').click(function () {
                    fwdLogin(old_system);
                });
                $('#new-edition-btn').click(function () {
                    fwdLogin(new_edition);
                });
                $('#modal-close-btn').click(function () {
                    $('#edition-panel').modal('hide');
                });
                function fwdLogin(contract_app) {
                    $('#edition-panel').modal('hide');
                    if (contract_app != null) {
                        var emailInput = $('#loginform-email').val();
                        if (contract_app === old_system) {
                            alert("You are logging in old system.");
                        }
                        if (contract_app === new_edition) {
                            window.location = '/pwd/'+emailInput;
                        }
                    } else {
                        alert('メールアドレスが存在しません');
                    }
                }*/
                function checkValidate() {
                    var $form = $('#loginform');
                    var flg = $form.validationEngine("validate");
                    if (flg == false) {
                        $form.validationEngine('updatePromptsPosition');
                        return false;
                    }
                    return true;
                }
                $('#loginform').validationEngine( 'hide',{
                    promptPosition: 'topLeft: 140',
                    autoPositionUpdate: true,
                    binded: false
                });
            });
        </script>
    </body>
</html>
