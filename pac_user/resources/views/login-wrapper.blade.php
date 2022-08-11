<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="{{config('app.name')}}">
    <meta name="keyword" content="{{config('app.name')}}">
    <title>{{config('app.name')}}</title>

    <script src="{{ asset('/js/libs/jquery/3.3.1/jquery-3.3.1.js') }}"></script>
</head>

<body>
<script>

    var isLogged = {{ \App\Utils\AutoLoginUtils::check() ? 1 : 0 }};
    console.log('isLogged: ' + isLogged);
    var loginUrl = '{{url('/login')}}';
    var target = '*';

    $(document).ready(function () {

        var eventMethod = window.addEventListener? "addEventListener": "attachEvent";
        var eventListener = window[eventMethod];
        var messageEvent = eventMethod === "attachEvent"? "onmessage": "message";
        var outer = parent;
        eventListener(messageEvent, function (e) {
            var eventName = null;
            if (e.data && e.data.event){
                eventName = e.data.event;
            }
            console.log('Api wrapper is received message ' + eventName);
            if (eventName == 'login'){
                // remove local storage
                localStorage.clear();

                // call api
                var username = e.data.data.email;
                var password = e.data.data.password;
                var rememberMe = e.data.data.remember;
                var return_url = e.data.data.return_url;
                var redirectUrl = getUrlVars()["redirectUrl"];

                if (username && password){
                    $.ajax({
                        type: 'POST',
                        url: loginUrl,
                        data: {username: username, password: password, remember: rememberMe, return_url: return_url, "_token": "{{ csrf_token() }}"},
                        success: function(data, textStatus, jqXHR){
                            switch (jqXHR.status){
                                case 200:
                                    outer.postMessage({event:'res_login', data:{responseCode:200, responseBody:{success: true, redirectUrl: redirectUrl?redirectUrl:(data.hasOwnProperty('redirectUrl')?data.redirectUrl:'{{rtrim(url("/"))}}/')}}}, target);
                                    break;
                                default :
                                    outer.postMessage({event:'res_login', data:{responseCode:jqXHR.status, responseBody:{success: false, message: data.message}}}, target);
                                    break;
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown){
                            let resJson = jqXHR.responseJSON || null;
                            switch (jqXHR.status){
                                case 403:
                                    outer.postMessage({event:'res_login', data:{responseCode:jqXHR.status, responseBody:{success: false, message: resJson.message || null }}}, target);
                                    break;
                                default:
                                    outer.postMessage({event:'res_login', data:{responseCode:jqXHR.status, responseBody:{success: false, message: null }}}, target);
                                    break;
                            }
                        }
                    });
                }
            }
        });

        parent.postMessage({event:'wrapper_ready', data:{}}, target);
        if (isLogged){
            parent.postMessage({event:'remember_login', data:{responseCode:200, responseBody:{success: true, redirectUrl: '{{url("/")}}/'}}}, target);
        }
    });

    function getUrlVars() {
        var mainUrl = (window.location != window.parent.location)
            ? document.referrer
            : document.location.href;
        var vars = {};
        var parts = mainUrl.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });
        return vars;
    }

</script>
<script>   document.oncontextmenu = function () {return false;} </script>
</body>

</html>