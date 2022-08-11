<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <!--  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no">
    -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Shachihata Cloud</title>
    <!-- Styles -->
    <style type="text/css">
        @font-face {
          font-family: "MS-Mincho";
          src: url('{{url('/fonts')}}/MS-Mincho.ttf?t=1525787366991') format('truetype');
        }
        @font-face {
          font-family: "MS-Gothic";
          src: url('{{url('/fonts')}}/MS-Gothic.ttf?t=1525787366991') format('truetype');
        }
        @font-face {
          font-family: "Meiryo";
          src: url('{{url('/fonts')}}/MS-Gothic.ttf?t=1525787366991') format('truetype');
        }
        @font-face {
          font-family: "shnmin";
          src: url('{{url('/fonts')}}/SHnmin-W5-01.ttf?t=1525787366991') format('truetype');
        }
        @font-face {
          font-family: "shnkgo";
          src: url('{{url('/fonts')}}/SHnkgo-W5-01.ttf?t=1525787366991') format('truetype');
        }
        @font-face {
          font-family: "shgyo";
          src: url('{{url('/fonts')}}/SHgyo-W5-01.ttf?t=1525787366991') format('truetype');
        }
    </style>
    <style type="text/css">
            @font-face {
              font-family: 'Noto Sans JP';
              font-style: normal;
              font-weight: 400;
              src: url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-regular.eot');
              src: local('Noto Sans Japanese Regular'), local('NotoSansJapanese-Regular'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-regular.eot?#iefix') format('embedded-opentype'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-regular.woff2') format('woff2'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-regular.woff') format('woff'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-regular.ttf') format('truetype'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-regular.svg#NotoSansJP') format('svg');
            }
            @font-face {
              font-family: 'Noto Sans JP';
              font-style: normal;
              font-weight: 500;
              src: url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-500.eot');
              src: local('Noto Sans Japanese Medium'), local('NotoSansJapanese-Medium'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-500.eot?#iefix') format('embedded-opentype'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-500.woff2') format('woff2'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-500.woff') format('woff'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-500.ttf') format('truetype'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-500.svg#NotoSansJP') format('svg');
            }
            @font-face {
              font-family: 'Noto Sans JP';
              font-style: normal;
              font-weight: 700;
              src: url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-700.eot');
              src: local('Noto Sans Japanese Bold'), local('NotoSansJapanese-Bold'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-700.eot?#iefix') format('embedded-opentype'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-700.woff2') format('woff2'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-700.woff') format('woff'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-700.ttf') format('truetype'),
                   url('{{url('/fonts')}}/noto-sans-jp-v25-japanese_latin-700.svg#NotoSansJP') format('svg');
            }
        </style>
    <link rel="stylesheet" href="{{ asset(mix('css/main.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/iconfont.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/material-icons/material-icons.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/vuesax.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/prism-tomorrow.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/app.css')) }}">
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">

    <script src='{{ asset('js/polyfill.min.js') }}'></script>
    <script src='{{ asset('pac_js/pac-utils.js') }}?v=2'></script>
    <script language="javascript">
        if(typeof(UserAgentInfo) != 'undefined' && !window.addEventListener) {
            UserAgentInfo.strBrowser=1;
        }
    </script>
    <script type="text/javascript">
        /**
         * trimEnd, trimRight polyfill
         */

        if (!HTMLElement.prototype.scrollTo) { HTMLElement.prototype.scrollTo = function (left, top) {this.scrollTop = top; this.scrollLeft = left; } }
        if(!String.prototype.trimLeft) {
            String.prototype.trimLeft =  function() {
                return this.replace(/^\s+/g,'');
            };
        }
        if(!String.prototype.trimRight) {
            String.prototype.trimRight = function() {
                return this.replace(/\s+$/g,'');
            };
        }
        if (window.NodeList && !NodeList.prototype.forEach) {
          NodeList.prototype.forEach = Array.prototype.forEach;
        }

        Array.prototype.move = function(from, to) {
            this.splice(to, 0, this.splice(from, 1)[0]);
        };
</script>
    @if(Session::has('resultLogin') && !Session::has('needResetPass'))
      <script>
          var resultLogin = JSON.parse(Encryption.decode('{!! base64_encode(json_encode(Session::get('resultLogin'))) !!}'));

          sessionStorage.setItem('token', resultLogin.token);
          localStorage.setItem('expires_time', new Date().getTime() + resultLogin.expires_in * 1000 );
          storeLS('user', JSON.stringify(resultLogin.user));
          storeLS('limit', JSON.stringify(resultLogin.limit));
          localStorage.setItem('admin', JSON.stringify(resultLogin.admin));
          storeLS('branding', JSON.stringify(resultLogin.branding));
          localStorage.setItem('return_url', "{{ Config::get('app.unauthenticated_redirect_url','http://localhost/') }}");
          localStorage.setItem('api_host', "{{ Config::get('app.api_host','http://localhost/') }}");
          localStorage.setItem('vuejs__login', JSON.stringify({value: resultLogin.user.id, expire: null }));
      </script>
  @endif
    @if(config('analytics.provider'))
    {!! Analytics::render() !!}
    @endif
  </head>
  <body>
    <noscript>
      <strong>We're sorry but Application doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
    </noscript>
    <div id="app">
    </div>

    <!-- <script src="js/app.js"></script> -->
    <script src="{{ asset(mix('js/app.js')) }}"></script>
    <!--<script>   document.oncontextmenu = function () {return false;} </script>-->
  </body>
</html>
