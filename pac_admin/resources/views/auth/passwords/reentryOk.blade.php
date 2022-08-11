@extends('../layouts.basic')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card-group">
                <div class="card card-cascade narrower">
                    <div class="card-header " style="background-color: #00873c;color:white;">パスワード設定</div>
                    <div class="card-body text-left">
                        <div class="row col-md-12 col-sm-12 col-xs-12">
                            <p>ご入力いただいたメールアドレスに、パスワード設定用のリンクを記載したメールを送信しました。<br>ご確認ください。</p>
                            <div class="panel panel-warning col-md-12 col-sm-12 col-xs-12">
                                <div class="panel-heading">
                                    メールが届かない場合は、お使いのソフトの設定をご確認願います。
                                </div>
                                <div class="panel-body">
                                    <p>◆携帯電話、スマートフォンの場合<br>迷惑メールフィルタリング設定によって受信できない場合があります｡<br>指定受信リストに Shachihata Cloud のドメイン（ex.shachihata.co.jp）を登録してください。</p>
                                    <p>◆パソコンの場合<br>お使いのセキュリティソフトで振り分けられている場合があります。<br>迷惑メールフォルダをご確認ください。</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-5 col-md-5 form-group text-center ">
                                <a href="/admin/" class="btn " style="background-color: #00873c;color:white;">ログイン画面へ移動</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style type="text/css">
.panel-heading{
    color: #8a6d3b;
    background-color: #fcf8e3;
    border-top: 1px solid #faebcc;
    border-left: 1px solid #faebcc;
    border-right: 1px solid #faebcc;
    padding: 10px 15px;
    border-bottom: 1px solid transparent;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
}
.panel-body{
    padding:15px;
    border: 1px solid #faebcc;
}
.panel-warning{
    border-color: #faebcc;
}
.panel{
    margin-bottom: 20px;
    padding-left: 0;
}
.status-bar{
    background-color: #00873c !important;
}
</style>
