<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>認証コードの入力</title>
    <link rel="stylesheet" href="{{ asset('/css/libs/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/mfa.css') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <script src="{{ asset('/js/libs/jquery/1.9.1/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/libs/bootstrap/3.3.6/bootstrap.min.js') }}"></script>

</head>
<body>
@if ($terminate)
<div class="modal fade" id="modal-message" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&#215;</span></button>
                <p class="modal-title">認証コードの入力に失敗したため、はじめからログインをやり直してください。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@endif

<div id="loginbox">
    <div class="side-login">
        <form method="post" action="extra-auth" class="loginform">
            @csrf
            @if ($passwordRequired)
                <p>ご登録のメールアドレスに配信した認証コードとログインパスワードを入力してください。</p>
            @else
                <p>ご登録のメールアドレスに配信した認証コードを入力してください。</p>
            @endif
            <div style="display: none" id="refreshHref" adress="{{ route('mfa.resend') }}"></div>
            <label for="otp">認証コード</label><a id="resend" href="#">メールを再送信する</a>
            <input type="text" required="" name="otp" value="{{ old('otp') }}" class="form-control input-password" id="otp" maxlength="6" placeholder="000000">
            @if ($passwordRequired)
                <label for="password">パスワード</label>
                <input type="password" required="" name="password" value="" class="form-control input-password" id="password" placeholder="PASSWORD">
                <input type="checkbox" id="passcheck" tabindex="3" /> <label for="passcheck">パスワードを表示</label><br>
            @endif
            
            <input type="submit" class="btn btn-primary form-control" id="loginbtn" value="ログイン" />
        </form>
    </div>
    @if (session('message'))
        <div class="alert alert-danger">
            {{ session('message') }}
        </div>
    @endif
    @if (session('resend-message'))
        <div class="alert alert-info">
            {{ session('resend-message') }}
        </div>
    @endif
</div>
</body>
<script>
    $(function() {
        $('#passcheck').change(function() {
            if ($(this).prop('checked')) {
                $('#password').attr('type', 'text');
            } else {
                $('#password').attr('type', 'password');
            }
        });
        $('#loginbtn').click(function() {
           $('.alert').remove();
        });
        $('#resend').click(function () {
            send();
        })
        function sendMail(){
            let canClick = true;
            let fn = function(){
                if(canClick){
                    setTimeout(()=>{
                        window.location.href = $("#refreshHref").attr('adress');
                    },500);
                    canClick = false;
                }
                else{
                    setTimeout(()=>{
                        canClick = true;
                    },10000);
                }
            }
            return fn;
        }
        let send = sendMail();
        @if ($terminate)
        $('#modal-message').on('hidden.bs.modal',　function() {
            $.ajax({
                type: 'GET',
                url: '{{ url("/logout") }}',
                success: function(data, textStatus, jqXHR){
                    location.href = '{{ url("/") }}';
                },
                error: function(jqXHR, textStatus, errorThrown){
                    location.href = '{{ url("/") }}';
                }
            });
        });
        $('#modal-message').modal('show');
        @endif
    });
</script>
<script>   document.oncontextmenu = function () {return false;} </script>
</html>