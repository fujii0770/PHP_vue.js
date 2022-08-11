<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name')}}</title>
    <link rel="stylesheet" href="{{url('/css/app.css')}}">
    <?php
    if(Session::has('resultLogin')){
        ?>
        <script>
            var resultLogin = JSON.parse('{!!  Session::get('resultLogin') !!}');
             
            localStorage.setItem('token', resultLogin.token);
            localStorage.setItem('expires_time', new Date().getTime() + resultLogin.expires_in * 1000 );
            localStorage.setItem('user', JSON.stringify(resultLogin.user));
            localStorage.setItem('return_url', "{{Session::get('return_url')}}");
            localStorage.setItem('api_host', "{{Session::get('api_host')}}");
        </script>
        <?php
    }
    ?>
    @if(config('analytics.provider'))
        {!! Analytics::render() !!}
    @endif
</head>
<body>
    <div id="app"></div>
    <script src="{{ url('js/app.js') }}"></script>
</body>
<script>   document.oncontextmenu = function () {return false;} </script>
</html>
