<div style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
    <div style="width:100%;border-bottom:solid 1px gray;background-color:#107fcd">
        <img src="{{ $message->embed(public_path('logo.png')) }}" style="border-width:0px" class="CToWUd">
    </div>
    <div style="padding:16px 8px;background-color:white">
        <p>{{$receiver_name}} さん：</p>
        <p>{{$return_user}} さんから以下の回覧文書が差戻しされました。</p>

        <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tr>
                <td style="width: 100px">件名</td>
                <td style="width: 20px">：</td>
                <td>{{$mail_name}}</td>
            </tr>
            <tr>
                <td style="width: 100px;vertical-align: text-top">メッセージ</td>
                <td style="width: 20px;vertical-align: text-top">：</td>
                <td>
                    @foreach(explode("\n", $text) as $text)
                        {{$text}}<br/>
                    @endforeach
                <td>
            </tr>
            <tr>
                <td style="width: 100px;vertical-align: text-top">ファイル名</td>
                <td style="width: 20px;vertical-align: text-top">：</td>
                <td style="text-align: left">
                    @foreach($filenames as $filename)
                        {{$filename}}<br/>
                    @endforeach
                </td>
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

        @if(isset($hide_circular_approval_url) && !$hide_circular_approval_url)
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
                        <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px" href="{{$circular_approval_url}}" target="_blank" class="m_-6849224559476667371link-button">回覧文書をみる</a></td>
                    <td style="width:20%"></td>
                </tr>
                </tbody>
            </table>
            <br>
        @endif
        <p>Shachihata Cloudを利用して、回覧文書を確認・
            <wbr>捺印することができます。
            <br>文書をクリックするとShachihata Cloudの画面に移動します
            <wbr>。
            <br>
            <br>
            @if(!$hide_thumbnail_flg)
                <img style="width: 100%;border: solid 1px whitesmoke;" alt="Preview" src="{{ $message->embed($image_path) }}">
            @endif
        </p>

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
                    <a style="color:#fff;text-decoration: none;display: block;margin: 8px 20px;" href="{{$env_app_url}}" target="_blank" data-saferedirecturl="" class="m_-6849224559476667371link-button">{{\App\Http\Utils\AppUtils::getMailLoginUrlLabel($env_app_url)}}</a></td>
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