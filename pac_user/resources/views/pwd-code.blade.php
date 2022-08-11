@extends('../basic')

<script>
    window.onload=function(){
        // コード入力イベント
        $('#form-send-code').submit(function () {
            // remove local storage
            localStorage.clear();

            var code = $("#code").val();
            var email = $("#email").val();
            $.ajax({
                type: 'POST',
                url: '{{url('/password-code/getPasswordChangeUrl')}}',
                dataType: "json",
                data: {code,email},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(data, textStatus, jqXHR){
                    if(jqXHR.status === 203){
                        $("#modalError .message").html('入力されたパスワード設定コードが一致しません。<br>'+
                        '入力内容をご確認のうえ再度、入力をお願いいたします。');
                        $("#modalError").modal();
                    } else if(jqXHR.status === 201) {
                        $("#modalError .message").html('パスワード設定コード試行が規定回数を超えました。<br>'+
                            '3分後に再開できます。');
                        $("#modalError").modal();
                    } else if(jqXHR.status === 200) {
                        $("#modal .message").html('コードの確認ができました。パスワード変更画面を表示します。<br>');
                        $("#modal").modal();
                        if (transition_btn.hasChildNodes()){
                            var d = document.getElementById("transition_btny");
                            var d_nested = document.getElementById("link_btn");
                            var throwawayNode = d.removeChild(d_nested);
                        }
                        const a1 = document.createElement("a");
		                a1.href = data.link_reset;
                        a1.id = 'link_btn';
		                a1.innerText = "表示";
                        a1.classList.add('transition_btn_color');
		                transition_btn.appendChild(a1);
                    } else if(jqXHR.status === 204) {
                        $("#modalError .message").html('有効期日が過ぎています。<br>'+
                        '再度コードを生成してください。');
                        $("#modalError").modal();
                    }else{
                        $("#modalError .message").html(data.message || '成功だけど失敗');
                        $("#modalError").modal();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    //$("#modalError .message").html('エラーが発生しています。');
                    $("#modalError .message").html(resJson.message);
                    $("#modalError").modal();
                }
            });
            return false;
        });
    };
</script>

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8 col-xl-6">
            <div class="card-group">
                <div class="card card-cascade narrower">
                    <div class="card-header" style="background-color: white;">パスワード設定</div>
                    <div class="card-body">
                        <form id="form-send-code" action="javascript:void(0)" method="POST">
                            {{csrf_field()}}
                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        パスワード設定コードを入力して[送信]ボタンをクリックしてください。
                                    </p>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-sm-4 control-label">メール</label>
                                <div class="col-md-6 col-sm-6">
                                    <input id="email" type="email" name="email" style="width:100%" class="form-control" required>
                                    <input hidden name="type" value="2">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-sm-4 control-label">パスワード設定コード</label>
                                <div class="col-md-6 col-sm-6">
                                    <input id="code" type="code" name="code" style="width:100%" class="form-control" required>
                                    <input hidden name="type" value="2">
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="submit"  style="background-color: #0984e3;color:white;" class="btn" id="send-mail" value="送信">
                                </div>
                            </div>

                            <div class="form-group text-left">
                                <a href="/app/" class="btn" style="border:1px solid #ccc;text-decoration:none;color:black;padding:6px 12px;">ログイン画面へ移動</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
<script>   document.oncontextmenu = function () {return false;} </script>
@includeif('auth.Code.modal')
@includeif('auth.Code.errorModal')
<style>
    .status-bar{
        background-color: #0984e3 !important;
    }
    .transition_btn_color{
        color: #212529;
    }
    .transition_btn_color:hover{
        color: #212529;
        text-decoration: none;
    }
    .transition_btn{ 
        padding: 7px 30px;
        display: inline-block;
        border-radius: 5px;
        background: #fff;
        color: #212529;
    }
</style>
