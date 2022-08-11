<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="{{config('app.name')}}">
    <meta name="keyword" content="{{config('app.name')}}">
    <!-- <link rel="shortcut icon" href="assets/ico/favicon.png"> -->

    <title>{{config('app.name')}}</title>

    <!-- Icons -->
    <link href="{{ asset('/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/simple-line-icons.css') }}" rel="stylesheet">

    <!-- Main styles for this application -->
    <link href="{{ asset('/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/libs/mdbootstrap/4.8.2/mdb.min.css') }}" rel="stylesheet">
    <!-- Styles required by this views -->
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">
    <style>
        .app-footer {
            padding-top: 15px;
            padding-bottom: 15px;
        }
        @media (min-width: 320px) and (max-width: 767px) {
            .card {
                box-shadow: none;
            }
            .app {
                background-color: #fff;
            }
        }

    </style>
</head>

<body>
@include('panel.navbar')
<div class="app flex-row align-items-center" style="min-height: calc(100vh - 110px)">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card-group">
                <div class="card p-4">
                    <div class="card-body">
                        <h1 class="mb-4">LOGIN</h1>
                        @if($errors->any())
                            <div class='alert alert-danger' style="margin-bottom: 1rem">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                {{$errors->first()}}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('login') }}">
                            {{ csrf_field() }}
                            <div class="form-group mb-3">
                                <label for="name"><strong>ユーザID</strong></label>
                                <div class="input-group">
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required
                                           autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="name"><strong>Password</strong></label>
                                <div class="input-group ">
                                    <input type="{{ old('showPassword') ? 'text': 'password' }}" name="password" class="form-control"
                                           required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="custom-control-label" for="showPassword"><input type="checkbox" id="showPassword" {{ old('showPassword') ? 'checked': '' }} name="showPassword"> パスワードを表示</label>
                            </div>

                            <div class="row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary px-4 btn-lg btn-block">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@include('panel.footer')
<!-- Bootstrap and necessary plugins -->
<script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
<script src="{{ asset('js/vendor/popper.min.js') }}"></script>
<script src="{{ asset('js/vendor/bootstrap.min.js') }}"></script>
<script>
    $('#showPassword').change(function () {
        if($(this).is(':checked')) {
            $('input[name="password"]').attr('type', 'text');
        }else {
            $('input[name="password"]').attr('type', 'password');
        }
    });
</script>
</body>
</html>