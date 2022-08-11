<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1" /> 
        <meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
        <title>@yield('title', isset($meta['title'])?$meta['title']:config('app.name'))</title>
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

        <link href="{{ asset('/css/css.css') }}" rel="stylesheet">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="{{ asset('/css/bootstrap.min.css') }}">
        {{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" />--}}
        <link rel="stylesheet" href="{{ asset('/css/libs/font-awesome/5.11.2/all.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('/css/libs/jqueryui/1.12.1/jquery-ui.css') }}">
        @if(isset($meta['css']) && count($meta['css']))
                {!! implode("\n ", $meta['css']) !!}
        @endif
        <script src="{{ asset('/js/babel.min.js') }}"></script>
        <script src="{{ asset('/js/polyfill.min.js') }}"></script>
        <!-- jQuery library -->
        <script src="{{ asset('/js/libs/jquery/3.4.1/jquery.min.js') }}"></script>
        <script src="{{ asset('/js/libs/jqueryui/1.12.1/jquery-ui.min.js') }}"></script>

        <!-- Popper JS -->
        <script src="{{ asset('/js/libs/popper.js/1.14.7/umd/popper.min.js') }}"></script>

        <!-- Latest compiled JavaScript -->
        <script src="{{ asset('/js/libs/bootstrap/4.3.1/bootstrap.min.js') }}"></script>
        <script src="{{ asset('/js/libs/font-awesome/5.11.2/all.js') }}"></script>
        @if ($use_angular)
            <script src="{{ asset('/js/libs/angularjs/1.7.8/angular.min.js') }}"></script>
            <script src="{{ asset('/js/libs/angularjs/1.7.8/angular-sanitize.js') }}"></script>
        @endif

        <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
        @stack('styles_before')
        
        <script src="{{ asset('/js/libs/jquery.slimscroll.min.js') }}"></script>
        @if(isset($meta['js']) && count($meta['js']))
                {!! implode("\n ", $meta['js']) !!}
        @endif
        @if(config('analytics.provider'))
            {!! Analytics::render() !!}
        @endif
    </head>
    <body ng-app="PacAdmin" class="@if(!isset($show_sidebar) || !$show_sidebar) no-sidebar @endif">
        @if(isset($show_sidebar) && $show_sidebar)
            <div class="sidebar {!! Session::get('isNavMenuActive') == 1 ? 'active' : '' !!}" id="collapsibleNavbar">
            <div class="sidebar-content">
                <div class="header-sidebar flex justify-content-between">
                    <div class="flex items-center">
                        <a href="{{url('/')}}/"><img class="logo" src="{{ asset('images/logo.png') }}" /></a>
                        <p class="text">{!! config('app.slogan')!!} @if( config('app.pac_app_env') == 1 && session('system_name'))  <br/> {!! session('system_name') !!} @endif</p>
                    </div>
                    <div class="feather nav-menu-active" onclick="navMenuActive(1)" onselectstart="return false">
                        <svg xmlns="http://www.w3.org/2000/svg" id="Capa_1" enable-background="new 0 0 30 30" height="20px" viewBox="0 0 512 512" width="20px"><g><g><path d="m330.274 0-10.607 10.607c-24.914 24.914-28.585 63.132-11.047 91.987l-107.305 72.504-1.856-1.856c-40.939-40.939-107.553-40.94-148.492 0l-10.607 10.606 133.289 133.289-173.649 173.65 21.213 21.213 173.649-173.65 133.29 133.29 10.607-10.607c40.94-40.94 40.939-107.553 0-148.492l-1.856-1.856 72.504-107.305c28.855 17.539 67.073 13.868 91.987-11.047l10.606-10.606zm-3.187 428.148-243.235-243.235c29.104-19.248 68.783-16.069 94.394 9.541l139.3 139.3c25.61 25.611 28.789 65.29 9.541 94.394zm-11.791-139.07-92.374-92.374 105.496-71.281 58.159 58.159zm101.245-117.958-75.66-75.66c-13.828-13.828-16.758-34.491-8.789-51.216l135.665 135.665c-16.725 7.969-37.388 5.039-51.216-8.789z" data-original="#000000" data-old_color="#000000" fill="#0984E3" class="active-path"></path></g></g></svg>
                    </div>
                    <div class="feather nav-menu-not-active" onclick="navMenuActive(0)" onselectstart="return false">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 0 20 20" width="20px" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"><g id="surface1"><path d="M 12.902344 0 L 12.488281 0.414062 C 11.515625 1.386719 11.371094 2.878906 12.054688 4.007812 L 7.863281 6.839844 L 7.792969 6.765625 C 6.191406 5.167969 3.589844 5.167969 1.992188 6.765625 L 1.578125 7.179688 L 6.78125 12.386719 L 0 19.171875 L 0.828125 20 L 7.613281 13.21875 L 12.820312 18.421875 L 13.234375 18.007812 C 14.832031 16.410156 14.832031 13.808594 13.234375 12.207031 L 13.160156 12.136719 L 15.992188 7.945312 C 17.121094 8.628906 18.613281 8.484375 19.585938 7.511719 L 20 7.097656 Z M 12.777344 16.722656 L 3.277344 7.222656 C 4.414062 6.472656 5.960938 6.59375 6.960938 7.597656 L 12.402344 13.039062 C 13.40625 14.039062 13.527344 15.585938 12.777344 16.722656 Z M 12.316406 11.292969 L 8.707031 7.683594 L 12.828125 4.898438 L 15.101562 7.171875 Z M 16.269531 6.683594 L 13.316406 3.730469 C 12.777344 3.1875 12.660156 2.382812 12.972656 1.726562 L 18.273438 7.027344 C 17.617188 7.339844 16.8125 7.222656 16.269531 6.683594 Z M 16.269531 6.683594 " style="stroke: none; fill-rule: nonzero; fill: rgb(9, 132, 227); fill-opacity: 1;"></path> <path d="M 18.5 18.5 L 493.5 494.5 " transform="matrix(0.0390625,0,0,0.0390625,0,0)" style="fill: none; stroke-width: 32.5; stroke-linecap: butt; stroke-linejoin: miter; stroke: rgb(9, 132, 227); stroke-opacity: 1; stroke-miterlimit: 4;"></path></g></svg>
                    </div>
                </div>
                @include('layouts.navbar')
                </div>
                <div class="backdrop"></div>
            </div>
        @endif
        
        @section('status-bar')
            <div class="status-bar {!! Session::get('isNavMenuActive') == 1 ? 'padding-left-215' : '' !!}">
                <div class="container-fluid">
                    @if(isset($show_sidebar) && $show_sidebar)
                        <a class="toogle-menu"><i class="fa fa-bars icon" style="font-size: 1.5em"></i></a>
                    @endif
                    @if ($use_contain)
                    <h1 class="page-title">{{ isset($meta['title'])?$meta['title']:config('app.name') }}</h1>
                    @endif
                    @if (Auth::check())
                        <div class="user-meta right text-right">
                            <div class="dropdown">
                                <span class="user-name dropdown-toggle" data-toggle="dropdown">{{ Auth::user()->getFullName() }}</span>
                                <div class="dropdown-menu action dropdown-menu-right">
                                    <span class="dropdown-menu-arrow"></span>
                                    @if (session(\App\Http\Utils\AppUtils::SESSION_ADMIN_HAS_USER_ACCOUNT))
                                        <button class="dropdown-item px-2" type="submit" id="loginUserByAdmin"><i class="fas fa-user-shield "></i> 利用者としてログイン </button>
                                    @endif
                                    <!-- PAC_5-1032 StandardとBusinessでヘルプのリンク先を変更したい -->
                                    @if(session('contract_edition') == 0 || session('contract_edition') == 3)
                                        <a class="dropdown-item px-2" href="https://help.dstmp.com/scloud/standard/" target="_blank"><i class="fas fa-info"></i> ヘルプ</a>
                                    @elseif(session('contract_edition') == 1 || session('contract_edition') == 2)
                                        <a class="dropdown-item px-2" href="https://help.dstmp.com/scloud/business/" target="_blank"><i class="fas fa-info"></i> ヘルプ</a>
                                    @endif
                                    <a class="dropdown-item px-2" href="{{ url('/logout') }}"><i class="fas fa-power-off"></i> ログアウト</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @if (session(\App\Http\Utils\AppUtils::SESSION_ADMIN_HAS_USER_ACCOUNT))
                <form id="loginUserByAdminForm" method="POST">
                    <div class="modal modal-detail" id="modalLoginUser" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <!-- Modal body -->
                            <div class="modal-body form-horizontal">
                            <!-- <div class="message message-info"></div>-->
                                <div class="message-info"></div>
                                <div class="form-group">
                                    <input type="hidden" name="username" value="{{ Auth::user()->email }}">
                                    <label for="company_name" class="control-label">パスワードを入力してください </label>
                                    <div class="mb-2">
                                        <input type="password" class="form-control password" name="password" value="" />
                                        <input type="hidden" name="from_admin" value="true" />
                                        <input type="hidden" name="return_url" value="{{url('/login')}}" />
                                    </div>
                                    <div>
                                        <input type="checkbox" value='1' id="checkTypePass" />
                                        <label for="checkTypePass">パスワードを表示</label>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="modal-footer">
                                <div><input type="submit" value="ログイン" class="btn btn-primary" id="loginUser"></div>
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <i class="fas fa-times-circle"></i> 閉じる
                                </button>
                            </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        @show
        @if ($use_contain)
            <div class="page-inner container-fluid {!! Session::get('isNavMenuActive') == 1 ? 'padding-left-215' : '' !!}">
                <div class="main-contain">
                   {{ \App\Http\Utils\CommonUtils::showMessage() }}
                    @yield('content')
                </div>
            </div>
        @else
            <div class="page-inner {!! Session::get('isNavMenuActive') == 1 ? 'padding-left-215' : '' !!}">
                {{ \App\Http\Utils\CommonUtils::showMessage() }}
                @yield('content_home')
            </div>
        @endif

        @if ($use_angular)
            @include('layouts.loading')
            @include('layouts.modalconfirm')
            @include('layouts.modalalert')
            @include('layouts.modallongtermconfirm')
            @include('layouts.modalalertconfirm')
            @include('layouts.modal_password_code_table')
            @include('layouts.modallongtermsaveconfirm')
        @endif

        <script src="{{ asset('/js/app.js') }}">
        </script>
        <script>   document.oncontextmenu = function () {return false;} </script>
        <script type="text/javascript">
            $("#checkTypePass").click(function () {
                if ($(".password").attr("type")=="password") {
                    $(".password").attr("type", "text");
                }
                else{
                    $(".password").attr("type", "password");
                }
            });
            $(document).ready(function() {
                $('#loginUserByAdmin').click(function() {
                    $("#modalLoginUser").modal();
                })
                $('#loginUser').click(function(ev) {
                    // PAC_5-1890 デフォルトのイベントをブロックする
                    ev.preventDefault();
                    $('.loading').removeClass('hide').addClass('show')
                    $.ajax({
                        type: "POST",
                        url: '{{config("app.url_app_user")}}/login',
                        data: $('#loginUserByAdminForm').serialize(), // serializes the form's elements.
                        statusCode: {
                            200: function(xhr) {
                                $.ajax({
                                    type: "GET",
                                    url: '{{ url('/logout') }}',
                                    complete: function(data) {
                                        $('input[name=from_admin]').val('');
                                        $('#loginUserByAdminForm').attr('action', '{{config("app.url_app_user")}}/login');
                                        $('#loginUserByAdminForm').submit();
                                        $('.loading').removeClass('show').addClass('hide')
                                    }
                                });
                            },
                            203: function(xhr) {
                                $('.loading').removeClass('show').addClass('hide')
                                $("#loginUserByAdminForm .message-info").append(showMessages(['パスワードが正しくありません'], 'danger', 10000));
                            },
                            500:function(xhr){
                                $('.loading').removeClass('show').addClass('hide')
                                $("#loginUserByAdminForm .message-info").append(showMessages(['パスワードが正しくありません'], 'danger', 10000));
                            }
                        }
                    });
                })
                if (GetIEVersion() > 0){
                    $.datepicker.regional.ja = {
                        closeText: "閉じる",
                        prevText: "&#x3C;前",
                        nextText: "次&#x3E;",
                        currentText: "今日",
                        monthNames: [ "1月","2月","3月","4月","5月","6月",
                            "7月","8月","9月","10月","11月","12月" ],
                        monthNamesShort: [ "1月","2月","3月","4月","5月","6月",
                            "7月","8月","9月","10月","11月","12月" ],
                        dayNames: [ "日曜日","月曜日","火曜日","水曜日","木曜日","金曜日","土曜日" ],
                        dayNamesShort: [ "日","月","火","水","木","金","土" ],
                        dayNamesMin: [ "日","月","火","水","木","金","土" ],
                        weekHeader: "週",
                        dateFormat: "yy/mm/dd",
                        firstDay: 0,
                        isRTL: false,
                        showMonthAfterYear: true,
                        yearSuffix: "年" };
                    $.datepicker.setDefaults( $.datepicker.regional.ja );

                    $('input[type="date"]').datepicker({
                        dateFormat: "yy/mm/dd"
                    });
                    $("input[type='checkbox']").attr('ondblclick', 'this.click()');
                }
            });
            function navMenuActive(navMenuActive) {
                if (navMenuActive == 1) {
                    $('.sidebar').addClass('active');
                    $('.status-bar,.page-inner').addClass('padding-left-215');
                    $('.folder_card').addClass('active-folder');
                } else {
                    $('.sidebar').removeClass('active');
                    $('.status-bar,.page-inner').removeClass('padding-left-215');
                    $('.folder_card').removeClass('active-folder');
                }
                $.ajax({
                    type: 'GET',
                    url: '{{url('/change-nav-menu-active')}}?navMenuActive='+navMenuActive,
                });
            }
        </script>
        <script>   document.oncontextmenu = function () {return false;} </script>

        @stack('styles_after')

        @stack('scripts')
    </body>
</html>