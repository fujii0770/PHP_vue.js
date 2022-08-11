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
                <h1 class="text-center">{{ config('app.name') }}</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <form action="{{ url('login') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="email">Email address:</label>
                                <input type="text" class="form-control" name="email" value="{{old('email', '')}}" />
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" name="password" value="" />
                                <input type="hidden" name="return_url" value="{{url('/login')}}" />
                            </div>
                            <div class="form-group">
                                
                                    <div class="text-center"><input type="submit" value="Login" class="btn btn-primary"></div>
                                
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
