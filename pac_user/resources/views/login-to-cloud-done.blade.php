<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <!-- Styles -->


    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">

    <script>
        let allowAccessCloud = {value: true, expire: null}
        let errorMessage = '{{$message}}';
        if(!errorMessage){
            localStorage.setItem('vuejs__{{$drive}}AccessToken', JSON.stringify(allowAccessCloud));
                   // $('h1').html('Login {{$drive}} access token success')
        }else{
            localStorage.setItem('vuejs__errormessage', JSON.stringify({value: errorMessage, expire: null }));
        }
        setTimeout(function(){
            window.close();
        }, 500);
    </script>
</head>
<body>
<script>   document.oncontextmenu = function () {return false;} </script>
<h1></h1>
</body>
</html>
