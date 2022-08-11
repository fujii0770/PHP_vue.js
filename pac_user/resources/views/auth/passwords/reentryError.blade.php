@extends('../basic')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card-group">
                <div class="card card-cascade narrower">
                    <div class="card-header " style="background-color: #0984e3;color:white;">パスワード設定</div>
                    <div class="card-body text-left">
                        <div class="row col-md-12 col-sm-12 col-xs-12">
                            <p>処理中にエラーが発生したので、パスワード設定用のリンクを記載したメールを送信しませんでした。</p>
                        </div>
                        <div class="form-group text-left">
                            <a href="{{url('/')}}/" class="btn" style="border:1px solid #ccc;text-decoration:none;color:black;padding:6px 12px;">ログイン画面へ移動</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style type="text/css">
    
</style>
