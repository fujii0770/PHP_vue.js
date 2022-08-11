<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name'))</title>
        <link rel="stylesheet" href="{{ asset('/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    </head>
    <body>
        <div class="container">
            <div class="row margin-top-50">
                <div class="col-sm-2 col-lg-3"></div>
                <div class="col-sm-8 col-lg-6">
                    <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <h3 class="text-center">権限がありません</h3>
                                <h3 class="text-center">管理者に連絡してください</h3>
                            </div>
                            <div class="form-group">
                                    <div class="text-center">
                                        <input type="submit" value="ログイン画面へ移動" class="btn btn-primary">
                                    </div>
                            </div>
                    </form>
                </div>
                <div class="col-sm-2 col-lg-3"></div>
            </div>
        </div>
        

        <script src="{{ asset('/js/libs/jquery/3.4.1/jquery-3.4.1.min.js') }}"></script>
        <script src="{{ asset('/js/libs/bootstrap/4.0.0/bootstrap.min.js') }}"></script>
    </body>
</html>
