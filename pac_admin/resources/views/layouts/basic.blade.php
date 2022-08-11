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
               {{ CommonUtils::showMessage() }}
                @yield('content')
            </div>
        </div>
        
        <script src="{{ asset('/js/app.js') }}"></script>

        @stack('styles_after')

        @stack('scripts')
    </body>
    <script>   document.oncontextmenu = function () {return false;} </script>
</html>