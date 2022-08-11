@extends('../basic')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8 col-xl-6">
            <div class="card-group">
                <div class="card card-cascade narrower">
                    <div class="card-header" style="background-color: #0984e3;color:white;">パスワード設定</div>
                    <div class="card-body">
                        <form id="form-send-mail" action="{{url('reentry')}}" method="POST" onsubmit="return submitOnce(this)">
                            {{csrf_field()}}
                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        パスワード設定用のリンクをメールで送信します。<br />
                                        ユーザ名を入力して【送信】ボタンをクリックして下さい。
                                    </p>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-sm-4 control-label">ユーザ名</label>
                                <div class="col-md-6 col-sm-6">
                                    <input type="email" name="email" style="width:100%" class="input-email form-control"  required>
                                    <input hidden name="type" value="2">
                                </div>
                                <div class="col-md-2 col-sm-2">
                                    <input type="submit"  style="background-color: #0984e3;color:white;" class="btn" id="send-mail" value="送信">
                                </div>
                            </div>

                            <div class="form-group text-left">
                                <a href="{{url('/')}}/" class="btn" style="border:1px solid #ccc;text-decoration:none;color:black;padding:6px 12px;">ログイン画面へ移動</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .status-bar{
        background-color: #0984e3 !important;
    }
</style>

<script language="javascript">
    var submitcount=0;
    function submitOnce (form) {
        if (submitcount == 0) {
            submitcount++;
            return true;
        } else {
            return false;

        }
    }
</script>
