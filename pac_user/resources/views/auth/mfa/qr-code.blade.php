<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>認証してください</title>
    <link rel="stylesheet" href="{{ asset('/css/libs/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/mfa.css') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <script src="{{ asset('js/libs/jquery/1.9.1/jquery.min.js') }}"></script>
    <script src="{{ asset('js/libs/bootstrap/4.0.0/bootstrap.min.js') }}"></script>

</head>
<body>
<div class="modal fade" id="modal-message" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&#215;</span></button>
                <p class="modal-title">認証に失敗したため、はじめからログインをやり直してください。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<div id="loginbox">
    <div class="side-login">
        <div class="text-center">
            {!! QrCode::size(200)->generate($token) !!}
            {{-- PAC_5-1074 BEGIN QRコードの表示変更         --}}
            <p>Shachihata Cloud スマホアプリ で<br/>QRコードを読み取ってください</p>
            {{-- PAC_5-1074 END           --}}
        </div>
    </div>
    @if (session('message'))
        <div class="alert alert-danger">
            {{ session('message') }}
        </div>
    @endif
</div>
</body>
<script>
$(function() {
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
    function poll(time) {
        $.ajax({
            type: 'GET',
            url: '{{ url("/extra-auth/poll") }}',
            success: function(data, textStatus, jqXHR){
                switch (data.status){
                    case 200:
                        location.href = '{{ url("/") }}';
                        break;
                    case 203:
                        if (time < 3*60*1000) {
                            time += 5000;
                            setTimeout(poll, 5000, time);
                        } else {
                            $('#modal-message').modal('show');
                        }
                        break;
                    default :
                        $('#modal-message').modal('show');
                        break;
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                $('#modal-message').modal('show');
            }
        });
    }
    setTimeout(poll, 5000, 0);
});
</script>
<script>   document.oncontextmenu = function () {return false;} </script>
</html>