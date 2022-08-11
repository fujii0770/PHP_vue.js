<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="width:100%;border-bottom:solid 1px gray;background-color:#107fcd">
        <img src="{{ $message->embed(public_path('logo.png')) }}" style="border-width:0px" class="CToWUd">
    </div>
    <div style="padding:16px 8px;background-color:white">
        <p>
            ダウンロードファイルの準備が完了しました。
        </p>
        <p>
            ダウンロード期限内にファイルのダウンロードをお願い致します。
        </p>
        <dl>
            <dt>ファイル名：</dt>
            <dd>{{$filename}}</dd>
            <dt>ダウンロード期限：</dt>
            <dd>{{$dl_period}}</dd>
        </dl>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tbody>
            <tr>
                <td style="width:20%"></td>
                <td class="m_-6849224559476667371link-button" style="min-width: 140px;
    border: 1px solid whitesmoke;
    border-radius: 8px;
    text-align: center;
    vertical-align: middle;
    background-color: #107fcd;"><a style="color:#fff;text-decoration: none;width:100%;display: block;margin: 8px 20px;" href="{{ config('app.new_app_url') }}" class="m_-6849224559476667371link-button" target="_blank" data-saferedirecturl="">ログイン画面に移動</a></td>
                <td style="width:20%"></td>
            </tr>
            </tbody>
        </table>
        <p>&nbsp;</p>
        <section style="background-color: whitesmoke;
    padding: 1.0em;
    margin-left: 1em;
    margin-right: 1em;">
            <strong>この電子メールの内容をほかの人と共有しないでください</strong>
            <p>メールに記載された文書へのリンクを用いて、
                <wbr>Shachihata Cloudの文書にアクセスが可能です。
                <br> 他の人に見られることがないように、メールの転送、
                <wbr>および文書へのリンクの転記は控えてください。
            </p>
        </section>
    </div>
    <table id="m_-6849224559476667371mail-footer" style="width: 100%" cellpadding="0" cellspacing="0" border="0">
        <tbody>
        <tr>
            <td style="background-color:#107fcd;">
                <a style="color:#ffffff;padding:4px 8px" href="http://www.shachihata.co.jp" target="_blank" data-saferedirecturl="">©2020&nbsp;Shachihata Inc.</a>&nbsp; &nbsp;
                <a style="color:#ffffff;padding:4px 8px" href="https://www.shachihata.co.jp/policy/index.php" target="_blank" data-saferedirecturl="">プライバシーポリシー</a>&nbsp; &nbsp;
                <a style="color:#ffffff;padding:4px 8px" href="https://estamp.shachihata.co.jp/tou/" target="_blank" data-saferedirecturl="">会員規約</a>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="yj6qo"></div>
    <div class="adL">
    </div>
</div>
