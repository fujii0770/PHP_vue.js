<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Shachihata Cloud</title>
    <!-- Styles -->


    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">

    @if(Session::has('resultLogin'))
      <script>
          localStorage.setItem('{{$drive}}AccessToken', "{{$token}}");
      </script>
  @endif
  </head>
  <body>
    <h1>GET TOKEN SUCCESSFUL</h1>
    <script>   document.oncontextmenu = function () {return false;} </script>
  </body>
</html>
