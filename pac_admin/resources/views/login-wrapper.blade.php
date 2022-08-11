<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="{{config('app.name')}}">
    <meta name="keyword" content="{{config('app.name')}}">
    <title>{{config('app.name')}}</title>

    <script src="{{ asset('/js/libs/jquery/3.3.1/jquery-3.3.1.js') }}"></script>
</head>

<body>

<script>
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
    console.log('isLogged: ' + isLogged);
    var loginUrl = '{{route('login')}}';
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
                // call api
                var username = e.data.data.email;
                var password = e.data.data.password;
                var rememberMe = e.data.data.remember;
                if (username && password){
                    $.ajax({
                        type: 'POST',
                        url: loginUrl,
                        data: {email: username, password: password, remember: rememberMe, "_token": "{{ csrf_token() }}"},
                        success: function(data, textStatus, jqXHR){
                            switch (jqXHR.status){
                                case 200:
                                    var redirectUrl = '{{url("/")}}/';
                                    if (data.hasOwnProperty('redirectUrl')){
                                        redirectUrl = data.redirectUrl;
                                    }
                                    outer.postMessage({event:'res_login', data:{responseCode:200, responseBody:{success: true, redirectUrl: redirectUrl}}}, target);
                                    break;
                                default :
                                    outer.postMessage({event:'res_login', data:{responseCode:jqXHR.status, responseBody:{success: false, message: null }}}, target);
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
</script>
</body>

</html>