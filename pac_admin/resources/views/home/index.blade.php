@extends('../layouts.main')

@section('content_home')

    <div class="card-body text-center" style="background-color: #0984e3;height: calc(50vh - 60px);min-height: 400px">
        <div>
            <img class="logo" src="{{ asset('images/home_logo.png') }}" />
            <h2 class="mb-5 mt-5" style="color: #fff">　管理者画面へようこそ</h2>
            <br/>
            <img class="logo" src="{{ asset('images/home.png') }}" />
        </div>
    </div>
    <div class="card-body" style="height: calc(50vh - 80px);min-height: 380px;background-color: #e7f6fb;">
    </div>
    <div class="card-body" style="height: 80px;background-color: #e7f6fb;">
        <div class="row">
            <div style="width: 100px;"></div>
            <div style="width: calc(50vw - 100px);float: right;color: #7F898F">
                <label>version 00.00</label>
            </div>
            <div style="color:#7f898f;text-align: right;width: calc(50vw - 50px);float: right;">
                <div>
                    <!-- PAC_5-1032 StandardとBusinessでヘルプのリンク先を変更したい -->
                    @if(session('contract_edition') == 0 || session('contract_edition') == 3)
                        <a style="color:#7f898f" href="https://help.dstmp.com/scloud/standard/" target="_blank">ヘルプサイト</a>　/
                    @elseif(session('contract_edition') == 1 || session('contract_edition') == 2)
                        <a style="color:#7f898f" href="https://help.dstmp.com/scloud/business/" target="_blank">ヘルプサイト</a>　/　
                    @endif
                    <a style="color:#7f898f" href="https://www.shachihata.co.jp/policy/index.php" target="_blank">プライバシーポリシー</a>　/　
                    <a style="color:#7f898f" href="" target="_blank">お問い合わせ</a>
                </div>
                <div style="font-size: 10px;margin-top: 10px">
                    <a style="color:#7f898f" href="http://www.shachihata.co.jp" target="_blank">©2020&nbsp;Shachihata Inc.</a>
                </div>
            </div>
        </div>
    </div>


@endsection
