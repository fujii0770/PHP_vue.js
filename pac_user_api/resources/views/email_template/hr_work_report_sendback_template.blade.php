<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="width:100%;border-bottom:solid 1px gray;background-color:#107fcd">
        <img src="{{ $message->embed(public_path('logo.png')) }}" style="border-width:0px" class="CToWUd">
    </div>
    <div style="padding:16px 8px;background-color:white">
        <p>{{$receiver_name}} さん：</p>
        <p>{{$return_user}} さんから以下の勤務表が差戻しされました。</p>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tr>
                <td style="width: 100px">勤務月</td>
                <td style="width: 20px">：</td>
                <td>{{$mail_name}}</td>
            </tr>
            <tr>
                <td style="width: 100px">作成者</td>
                <td style="width: 20px">：</td>
                <td>
                    @if($author_email)
                        <a href="mailto:{{$author_email}}">{{$author_email}}</a>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="width: 100px">最終更新者</td>
                <td style="width: 20px">：</td>
                <td>
                    @if($last_updated_email)
                        <a href="mailto:{{$last_updated_email}}">{{$last_updated_email}}</a>
                    @endif
                </td>
            </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tbody>
            <tr>
                <td style="width:20%"></td>
                <td class="m_-6849224559476667371link-button" style="min-width: 140px;
    border: 1px solid whitesmoke;
    border-radius: 8px;
    text-align: center;
    vertical-align: middle;
    background-color: #107fcd;">
                    @if(isset($url_domain_id) && $url_domain_id)
                        <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px;" href="{{ config('app.new_app_url').'/'.config('app.saml_url_prefix').'/'.$url_domain_id }}">SAML機能でログイン</a>
                    @else
                        <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px;" href="{{ config('app.new_app_url') }}">ログイン画面に移動</a>
                    @endif
                </td>
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
                <!--<a style="color:#ffffff;padding:4px 8px" href="https://estamp.shachihata.co.jp/tou/" target="_blank" data-saferedirecturl="">会員規約</a>-->
            </td>
        </tr>
        </tbody>
    </table>
    <div class="yj6qo"></div>
    <div class="adL">
    </div>
</div>