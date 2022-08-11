<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="robots" content="noindex,nofollow">
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1" /> 
        <meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
        <title>@yield('title', isset($meta['title'])?$meta['title']:config('app.name'))</title>
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

        <link href="{{ asset('/css/libs/css.css') }}" rel="stylesheet">
        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="{{ asset('/css/libs/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/libs/font-awesome/5.11.2/all.min.css') }}" />
        @if(isset($meta['css']) && count($meta['css']))
                {!! implode("\n ", $meta['css']) !!}
        @endif

        <!-- jQuery library -->
        <script src="{{ asset('/js/libs/jquery/3.4.1/jquery.min.js') }}"></script>

        <!-- Popper JS -->
        <script src="{{ asset('/js/libs/popper.js/1.14.7/umd/popper.min.js') }}"></script>

        <!-- Latest compiled JavaScript -->
        <script src="{{ asset('/js/libs/bootstrap/4.3.1/bootstrap.min.js') }}"></script>
        <script src="{{ asset('/js/libs/font-awesome/5.11.2/all.js') }}"></script>
        <script src="{{ asset('/js/libs/angularjs/1.7.8/angular.min.js') }}"></script>
        <script src="{{ asset('/js/libs/angularjs/1.7.8/angular-sanitize.js') }}"></script>

        @stack('styles_before')
        
    </head>
    <body>
        
        @section('status-bar')
            <div class="status-bar">
                <div class="container-fluid">
                    @if(isset($show_sidebar) && $show_sidebar)
                        <a class="toogle-menu"><i class="fa fa-bars icon" style="font-size: 1.5em"></i></a>
                    @endif
                </div>
            </div>
        @show
        <div class="page-inner container-fluid">
            <div class="main-contain">
               
                @yield('content')
            </div>
        </div>
         
        @stack('styles_after')

        @stack('scripts')

        <style>
            body { font-family: "Noto Sans JP", MS Gothic; font-size: 14px; }
            .status-bar { background: #0984e3; color: #fff; height: 60px; position: fixed; width: 100%; top: 0; left: 0; z-index: 900; }
            .page-inner { padding-top: 60px; }
            .main-contain { padding-top: 20px; }
            .card-header { font-size: 18px; }
            .card-header.bg-primary { color: #fff; }
            .form-group .control-label { line-height: 38px; margin: 0; font-weight: 500; }
            .btn-success { background: #28C76F; border: none; }
            .btn-download { background: #0984E3; border: none; font-size: 20px; color: #FFFFFF ; width: 25%}
            .btn-default{ background: #d9d9d9; border: none; }
            .form-group .error { color: red; }
            .hide{ display: none; }
            @media screen and (min-width: 991px){
                .main-contain { margin-left: 80px; }
            }
        </style>
        <script>   document.oncontextmenu = function () {return false;} </script>
    </body>
</html>